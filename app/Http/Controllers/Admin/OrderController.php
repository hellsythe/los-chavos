<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Design;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\OrderDesign;
use App\Models\OrderNewDesign;
use App\Models\OrderUpdateDesign;
use App\Models\OrderCustomDesign;
use Sdkconsultoria\Core\Controllers\ResourceController;
use App\Models\Service;
use Illuminate\Http\Request;
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
                return $query->where(function($query) use ($value){
                    return $query->where('clients.name', 'like', '%'.$value.'%')
                        ->orWhere('clients.phone', 'like', '%'.$value.'%');
                });
            },
        ];
    }

    public function viewAny(Request $request)
    {
        $model = new $this->model;
        $this->authorize('viewAny', $model);

        $query = $model::where('orders.status', '>', $model::STATUS_ACTIVE);
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
        dd($request);
        $client = $this->saveClient($request->client);
        $order = $this->saveOrder($client, $request);
        $this->saveOrderDetails($order, $request);
        $this->savePayment($order,  $request['payment']);

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
        $order->created_by = auth()->user()->id;
        $order->garment_id = $request['garment']['data']['id'];
        $order->garment_amount = $request['garment']['amount'];
        $order->client_id = $client->id;
        $order->total = 0;

        if ($order->total == $request['payment']['advance'] ) {
            $order->status = Order::STATUS_PENDING;
        } else {
            $order->status = Order::STATUS_MISSING_PAYMENT;
        }

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
                //     $this->saveUpdateDesing($orderDetail, $service);
                    break;
                case 4:
                    $this->saveNewDesign($orderDetail, $service);
                    break;
            }
        }

        $order->save();
    }

    private function saveDesing($orderDetail, $service)
    {
        $model = new OrderDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->design_id = $service['design']['id'];
        $model->save();
    }

    private function saveNewDesign($orderDetail, $service)
    {
        $design = $this->createNewDesign($service);

        $model = new OrderNewDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->design_id = $design->id;
        $model->price = $service['price_new'];
        $model->save();
    }

    private function createNewDesign($service)
    {
        $design = new Design();

        return $design;
    }

    private function saveUpdateDesing($orderDetail, $service)
    {
        $model = new OrderUpdateDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->save();
    }

    private function saveCustom($orderDetail, $service)
    {
        $model = new OrderCustomDesign();
        $model->order_detail_id = $orderDetail->id;
        $model->text = $service['custom']['text'];
        $model->typography_id = $service['typography']['id'];
        $model->size = $service['textsize'];
        $model->save();
    }

    private function savePayment($order, $payment)
    {
        $paymentModel = new Payment();
        $paymentModel->created_by = auth()->user()->id;
        $paymentModel->order_id = $order->id;
        $paymentModel->amount = $payment['advance'];
        $paymentModel->save();
    }
}
