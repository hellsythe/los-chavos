<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use Sdkconsultoria\Core\Controllers\ResourceController;
use App\Models\Service;
use Illuminate\Http\Request;
use Sdkconsultoria\Core\Controllers\Traits\ApiControllerTrait;

class OrderController extends ResourceController
{
    use ApiControllerTrait;
    protected $view = 'back.order';

    protected $model = \App\Models\Order::class;

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
        $order->total = $request['payment']['total'];

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
            $serviceModel = new OrderDetail();
            $serviceModel->order_id = $order->id;
            $serviceModel->service_id = $service['service_id']['id'];
            $serviceModel->subservice_id = $service['subservice_id']['id'];
            $serviceModel->point_x = $service['point']['x'];
            $serviceModel->point_y = $service['point']['y'];
            $serviceModel->comments = $service['comments'] ?? null;
            $serviceModel->price = $service['price'];
            $serviceModel->total = $service['price'] * $request['garment']['amount'];
            $serviceModel->save();
        }
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
