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
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends ResourceController
{
    use ApiControllerTrait;
    protected $view = 'back.order';

    protected $model = \App\Models\Order::class;

    protected function customFilters($query, $request)
    {
        if (!$request->id && !$request->status && !$request->client && !$request->design) {
            return $query->where('orders.status', $this->model::STATUS_PENDING);
        }

        return  $query;
    }

    protected function filters(): array
    {
        return [
            'client' => function ($query, $value) {
                $clients_id = Client::where('clients.name', 'like', '%' . $value . '%')
                    ->orWhere('clients.phone', 'like', '%' . $value . '%')->get()->pluck('id')->toArray();

                return $query->whereIn('client_id', $clients_id);
            },
            'id' => function ($query, $value) {
                return $query->where('id', $value);
            },
            'status' => function ($query, $value) {
                return $query->where('status', $value);
            },
            'design' => function ($query, $value) {
                $designs = Design::where('name', 'like', "%$value%")->get()->pluck('id')->toArray();
                $detail_designs = OrderDesign::whereIn('design_id', $designs)->get()->pluck('order_detail_id')->toArray();
                $details = OrderDetail::whereIn('id', $detail_designs)->get()->pluck('order_id')->toArray();

                return $query->whereIn('id', $details);
            },
            'garment' => function ($query, $value) {
                $details = OrderDetail::where('garment_id', $value)->get()->pluck('order_id')->toArray();
                return $query->whereIn('id', $details);
            },
        ];
    }

    public function viewAny(Request $request)
    {
        $model = new $this->model;
        $this->authorize('viewAny', $model);
        $query  = new $model;
        $query = $this->customFilters($query, $request);
        $query = $this->searchable($query, $request)->with('client');
        $query = $this->applyOrderByToQuery($query, $request->input('order'));

        return $this->setPagination($query, $request);
    }

    public function show(Request $request, $id)
    {
        $model = $this->model::findModel($id);
        $this->authorize('view', new Order());

        return view($this->view . '.show', [
            'model' => $model,
            'order' => Order::with('client')->with('services')->find($id)->toArray(),
            'available_services' => Service::with('subservices')->get(),
        ]);
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
                (new WhatsappNotification())->sendOrderIsDeliveryNotification($order);
                break;
            case Order::STATUS_READY:
                if (!auth()->user()->hasRole(['super-admin', 'Bordador', 'Estampador'])) {
                    abort(403);
                }
                break;
            case Order::STATUS_READY:
                if (!auth()->user()->hasRole(['super-admin', 'Bordador',  'Estampador'])) {
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

    public function ticket($id)
    {
        $order = Order::findModel($id);
        $size = $order->services()->count() * 90;
        $pdf = Pdf::loadView('back.order.ticket', ['order' => $order]);
        $pdf->setPaper([0, 0, 210, 430 + $size]);

        return $pdf->stream();
    }

    public function etiqueta($id)
    {
        $order = Order::findModel($id);
        $pdf = Pdf::loadView('back.order.etiqueta', ['order' => $order]);
        $pdf->setPaper([0, 0, 300, 100]);

        return $pdf->stream();
    }

    public function notifyClientByWhatsapp($id)
    {
        $order = Order::findModel($id);

        if ($order->client->phone) {
            (new WhatsappNotification())->sendOrderIsReadyNotification($order);
            $order->whatsapp_notification = now();
            $order->save();
        }

        return redirect('admin/order/' . $id);
    }
}
