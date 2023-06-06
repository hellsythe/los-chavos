<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewOrder;
use App\Events\OrderAuth;
use App\Events\OrderAuthRequest;
use App\Http\Controllers\Controller;
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

class SalePointController extends Controller
{

    public function index()
    {
        $this->authorize('create', new Order());

        return view('back.sale_point.index', [
            'available_services' => Service::with('subservices')->get(),
            'order' => [
                'id' => '',
                'deadlinex' => '',
                'client' => [
                    'name' => '',
                    'phone' => '',
                    'email' => '',
                    'whatsapp' => '0',
                ],
                'services' => [
                    [
                        'detail' => [
                            'design' => []
                        ],
                        'service' => [],
                        'subservice' => [],
                    ]
                ],
                'payment' => [
                    'total' => ''
                ]
            ],
        ]);
    }

    public function update($id)
    {
        $this->authorize('create', new Order());
        $model = Order::with('client')->with('services')->find($id)->toArray();

        // dd($model);
        // return json_encode($model);
        return view('back.sale_point.update', [
            'available_services' => Service::with('subservices')->get(),
            'order' => $model
        ]);
    }

    private function saveOrder($request)
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

        return [
            'order' => $order,
            'ticket' => $this->generateTicket($order)
        ];
    }


    private function generateTicket($order)
    {
        $pdf = Pdf::loadView('back.order.ticket', ['order' => $order]);
        $pdf->setPaper([0,0,210,520]);

        Storage::put('public/tickets/'.$order->id.'.pdf', $pdf->output());

        return config('app.url').Storage::url('public/tickets/'.$order->id.'.pdf');
    }
}
