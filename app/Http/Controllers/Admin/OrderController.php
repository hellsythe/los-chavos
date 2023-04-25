<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewOrder;
use App\Events\OrderAuth;
use App\Events\OrderAuthRequest;
use App\Models\Client;
use App\Models\Design;
use App\Models\DesignPrint;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\OrderDesign;
use App\Models\OrderNewDesign;
use App\Models\OrderUpdateDesign;
use App\Models\OrderCustomDesign;
use App\Models\OrderDesignPrint;
use Sdkconsultoria\Core\Controllers\ResourceController;
use App\Models\Service;
use App\Services\WhatsappNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Sdkconsultoria\Core\Controllers\Traits\ApiControllerTrait;

class OrderController extends ResourceController
{
    use ApiControllerTrait;
    protected $view = 'back.order';

    protected $model = \App\Models\Order::class;

    protected function filters(): array
    {
        return [
            'client' => function ($query, $value) {
                $query->join('clients', 'clients.id', '=', 'orders.client_id');
                return $query->where(function ($query) use ($value) {
                    return $query->where('clients.name', 'like', '%' . $value . '%')
                        ->orWhere('clients.phone', 'like', '%' . $value . '%');
                });
            },
        ];
    }

    public function viewAny(Request $request)
    {
        $model = new $this->model;
        $this->authorize('viewAny', $model);

        $query = $model::where('orders.status', '>', $model::STATUS_ACTIVE);
        $query = $model::where('orders.status', '<', $model::STATUS_FINISH);
        $query = $this->searchable($query, $request)->with('client');
        $query = $this->applyOrderByToQuery($query, $request->input('order'));

        return $this->setPagination($query, $request);
    }

    public function salePoint()
    {
        $this->authorize('create', new $this->model);

        return view('back.sale.point', [
            'available_services' => Service::all()
        ]);
    }

    public function processOrder(Request $request)
    {
        $client = $this->saveClient($request->client);
        $order = $this->saveOrder($client, $request);
        $this->saveOrderDetails($order, $request);
        $this->savePayment($order,  $request['payment']);

        if ($request['extra']['whatsapp'] ?? false) {
            (new WhatsappNotification())->sendOrderTicketToClient($order);
        }

        return [
            'id' => $order->id
        ];
    }

    private function saveClient($client): Client
    {
        $clientModel = Client::where('phone', $client['phone'])->first();

        if (!$clientModel) {
            $clientModel = new Client();
        }

        $clientModel->status = Client::STATUS_ACTIVE;
        $clientModel->name = $client['name'];
        $clientModel->phone = $client['phone'];
        $clientModel->email = $client['email'] ?? '';
        $clientModel->save();

        return $clientModel;
    }

    private function saveOrder($client, $request)
    {
        $order = new Order();
        $order->order_number = $request['extra']['orderId'] ?? '0';
        $order->deadline = $request['extra']['date'];
        $order->created_by = auth()->user()->id;
        $order->garment_id = $request['garment']['data']['id'];
        $order->garment_amount = $request['garment']['amount'];
        $order->client_id = $client->id;
        $order->total = 0;
        $order->save();

        return $order;
    }

    private function saveOrderDetails($order, $request)
    {
        foreach ($request['services'] as $service) {
            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $order->id;
            $orderDetail->service_id = $service['service_id']['id'];
            $orderDetail->subservice_id = $service['subservice_id']['id'];
            $orderDetail->point_x = $service['point']['x'];
            $orderDetail->point_y = $service['point']['y'];
            $orderDetail->comments = $service['comments'] ?? null;
            $orderDetail->price = $service['price'];
            $orderDetail->total = $service['price'] * $request['garment']['amount'];
            $orderDetail->save();

            $order->total += $orderDetail->total;

            switch ($orderDetail->subservice_id) {
                case 1:
                    $this->saveDesing($orderDetail, $service);
                    break;
                case 2:
                    $this->saveCustom($orderDetail, $service);
                    break;
                case 3:
                    $detail = $this->saveUpdateDesing($orderDetail, $service, $request['garment']['amount']);
                    $order->total += $detail->price;
                    break;
                case 4:
                    $detail = $this->saveNewDesign($orderDetail, $service, $request['garment']['amount']);
                    $order->total += $detail->price;
                    break;
                case 5:
                    $detail = $this->savePrintDesign($orderDetail, $service, $request['garment']['amount']);

                    break;
            }
        }

        if ($order->total == $request['payment']['advance']) {

            if ($request['extra']['orderId'] ?? 0 == '1') {
                $order->status = Order::STATUS_WAITING_ORDER;
            } else {
                $order->status = Order::STATUS_PENDING;
                NewOrder::dispatch($order);
            }
        } else {
            $order->status = Order::STATUS_MISSING_PAYMENT;
        }

        $order->missing_payment = $order->total;
        $order->save();
    }

    private function saveDesing($orderDetail, $service)
    {
        $model = new OrderDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->design_id = $service['design']['id'];
        $model->save();
    }

    private function savePrintDesign($orderDetail, $service)
    {
        $model = new OrderDesignPrint();
        $model->order_detail_id = $orderDetail->id;
        $model->price = $service['price'];

        if ($service['is_new_design']) {
            if ($service['design_is_here']) {
                $print = $this->createNewPrintDesign($service, $service['new_print_file']);
                $model->design_print_id = $print->id;
            }
        } else {
            $model->design_print_id = $service['designFilePrint']['id'];
        }

        $model->save_design = $service['save_design'];
        $model->if_new_design = $service['is_new_design'];
        $model->save();
    }

    private function createNewPrintDesign($service, $fileData)
    {
        $ext = explode(';', $service['new_print_file']);
        $ext = explode('/', $ext[0])[1];

        $design = new DesignPrint();
        $design->created_by = auth()->user()->id;
        $design->price = $service['price'];
        $design->name = $service['print_name'];
        $design->media = 'prueba';
        $design->status = DesignPrint::STATUS_ACTIVE;
        $design->save();
        $design->media = URL::to('/storage/design-print/' . $design->id . '.' . $ext);
        $design->save();
        $this->saveFileDesign($fileData, $design->id, 'design-print', $ext);

        return $design;
    }

    private function saveNewDesign($orderDetail, $service, $garmentAmount)
    {
        $design = $this->createNewDesign($service, $service['newDesign']);

        $model = new OrderNewDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->design_id = $design->id;
        $model->price = $service['price_new'];

        if ($garmentAmount > config('app.max_garment')) {
            $model->price = 0;
        }

        $model->save();

        return $model;
    }

    private function createNewDesign($service, $fileData)
    {
        $design = new Design();
        $design->created_by = auth()->user()->id;
        $design->minutes = $service['puntadas'];
        $design->price = $service['price'];
        $design->name = $service['new_design_name'];
        $design->media = 'prueba';
        $design->status = Design::STATUS_ACTIVE;
        $design->save();
        $design->media = URL::to('/storage/design/' . $design->id . '.pdf');
        $design->save();
        $this->saveFileDesign($fileData, $design->id);

        return $design;
    }

    private function saveFileDesign($data, $id, $url = 'design', $ext = 'pdf')
    {
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        Storage::put('public/' . $url . '/' . $id . '.' . $ext, $data);
    }

    private function saveUpdateDesing($orderDetail, $service, $garmentAmount)
    {
        $desingCurrent = Design::withTrashed()->where('id', $service['design']['id'])->first();
        $service['new_design_name'] = $desingCurrent->name;
        $design = $this->createNewDesign($service, $service['updateDesign']);

        $model = new OrderUpdateDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->price = $service['price_update'];

        if ($garmentAmount > config('app.max_garment')) {
            $model->price = 0;
        }

        $model->old_design_id = $service['design']['id'];
        $model->design_id = $design->id;
        $model->save();
        $desingCurrent->delete();

        return $model;
    }

    private function saveCustom($orderDetail, $service)
    {
        $model = new OrderCustomDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->text = $service['custom']['text'];
        $model->typography_id = $service['typography']['id'];
        $model->size = $service['textsize'];
        $model->save();

        return $model;
    }

    private function savePayment($order, $payment)
    {
        $paymentModel = new Payment();
        $paymentModel->created_by = auth()->user()->id;
        $paymentModel->order_id = $order->id;
        $paymentModel->amount = $payment['advance'];
        $paymentModel->status = Payment::STATUS_ACTIVE;
        $paymentModel->save();
    }

    public function updateOrderStatus($id, $status)
    {
        $order = Order::findModel($id);
        $order->status = $status;

        switch ($status) {
            case Order::STATUS_ORDER_ARRIVED:
                if (!auth()->user()->hasRole(['super-admin', 'Punto de venta'])) {
                    abort(403);
                }
                if ($order->missing_payment > 0) {
                    $order->status = Order::STATUS_MISSING_PAYMENT;
                } else {
                    $order->status = Order::STATUS_PENDING;
                    NewOrder::dispatch($order);
                }
                break;
            case Order::STATUS_FINISH:
                if (!auth()->user()->hasRole(['super-admin', 'Punto de venta'])) {
                    abort(403);
                }
                break;
            case Order::STATUS_READY:
                if (!auth()->user()->hasRole(['super-admin', 'Bordador'])) {
                    abort(403);
                }
                break;
            case Order::STATUS_READY:
                if (!auth()->user()->hasRole(['super-admin', 'Bordador'])) {
                    abort(403);
                }
                break;
            case Order::STATUS_WAITING_AUTH:
                if (!auth()->user()->hasRole(['super-admin', 'Bordador', 'Punto de venta'])) {
                    abort(403);
                }
                $order->requested_by = auth()->user()->id;
                OrderAuthRequest::dispatch([
                    'id' => $order->id,
                    'message' => 'La orden # ' . $order->id . ' requiere autorización por parte de un administrador'
                ]);
                (new WhatsappNotification())->sendRequestNotification($order);
                break;
            case Order::STATUS_PENDING:
                if (!auth()->user()->hasRole(['super-admin'])) {
                    abort(403);
                }
                $order->authorized_by = auth()->user()->id;
                OrderAuth::dispatch([
                    'id' => $order->id,
                    'message' => 'La orden # ' . $order->id . ' fue autorizada por ' . auth()->user()->email
                ]);
                (new WhatsappNotification())->sendApprovedNotification($order);
                break;
            default:
                abort(403);
                break;
        }
        $order->save();

        return redirect('admin/order/' . $id);
    }
}
