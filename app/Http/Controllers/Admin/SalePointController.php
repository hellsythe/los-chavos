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
use Illuminate\Support\Facades\DB;

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

        return view('back.sale_point.update', [
            'available_services' => Service::with('subservices')->get(),
            'order' => $model
        ]);
    }

    public function saveOrder(Request $request)
    {
        DB::beginTransaction();

        $order = $this->findOrCreateOrder($request->order);
        $this->saveServices($request->order['services'], $order);
        $this->savePayment();

        DB::commit();

        return [
            'order' => $order,
            'request' => $request->order,
            'ticket' => $this->generateTicket($order)
        ];
    }

    protected function findOrCreateOrder($order): Order
    {
        $order_model = new Order();
        $order_model->deadline = $order['deadlinex'];
        $order_model->total = $order['total'];
        $order_model->created_by = auth()->user()->id;
        $order_model->client_id = $this->findOrCreateClient($order['client'])->id;
        $order_model->order_number = $order['order_number'];
        $order_model->save();

        return $order_model;
    }

    protected function findOrCreateClient($client): Client
    {
        $clientModel = Client::where('phone', $client['phone'])->first();

        if (!$clientModel) {
            $clientModel = new Client();
        }

        $clientModel->status = Client::STATUS_ACTIVE;
        $clientModel->name = $client['name'];
        $clientModel->phone = $client['phone'];
        $clientModel->email = $client['email'] ?? '';
        $clientModel->whatsapp = $client['whatsapp'] ?? 0;
        $clientModel->save();

        return $clientModel;
    }

    protected function saveServices($services, $order)
    {
        foreach ($services as $service) {
            $order_detail = $this->findOrCreateOrderDetail($service, $order);
        }
    }

    protected function findOrCreateOrderDetail($service, $order): OrderDetail
    {
        $detail = new OrderDetail();
        $detail->service_id = $service['service']['id'];
        $detail->subservice_id = $service['subservice']['id'];
        $detail->comments = $service['comments'] ?? null;
        $detail->garment_amount = $service['garment_amount'];
        $detail->garment_id = $service['garment']['id'];
        $detail->point_x = $service['point_x'];
        $detail->point_y = $service['point_y'];
        $detail->price = $service['price'];
        $detail->total = $service['total'] ?? '0';
        $detail->order_id = $order->id;
        $detail->save();

        $this->findOrOrderDetailDetail($service, $detail);

        return $detail;
    }

    protected function findOrOrderDetailDetail($service, $detail)
    {
        switch ($detail->subservice_id) {
            case 1:
                $this->saveExistingDesing($detail, $service);
                break;
            case 2:
                $this->saveCustom($detail, $service);
                break;
            case 3:
                $this->saveUpdateDesing($detail, $service);
                break;
            case 4:
                $this->saveNewDesign($detail, $service);
                break;
            case 5:
                $this->savePrintDesign($detail, $service);
                break;
        }
    }

    protected function saveExistingDesing($detail, $service)
    {
        $model = new OrderDesign();
        $model->order_detail_id = $detail->id;
        $model->design_id = $service['detail']['design']['id'];
        $model->save();
    }

    private function saveCustom($detail, $service)
    {
        $model = new OrderCustomDesign();
        $model->order_detail_id = $detail->id;
        $model->text = $service['detail']['text'];
        $model->typography_id = $service['detail']['typography']['id'];
        $model->size = $service['detail']['size'];
        $model->save();

        return $model;
    }

    private function saveNewDesign($detail, $service)
    {
        $design = $this->createNewDesign($service['detail']);

        $model = new OrderNewDesign();
        $model->order_detail_id = $detail->id;
        $model->design_id = $design->id;
        $model->price = $service['detail']['price'];
        $model->save();

        return $model;
    }

    private function createNewDesign($service)
    {
        $design = new Design();
        $design->created_by = auth()->user()->id;
        $design->minutes = $service['design']['minutes'];
        $design->price = $service['price'];
        $design->name = $service['design']['name'];
        $design->media = 'prueba';
        $design->status = Design::STATUS_ACTIVE;
        $design->save();
        $design->media = URL::to('/storage/design/' . $design->id . '.pdf');
        $design->save();
        $this->saveFileDesign($service['design']['file'], $design->id);

        return $design;
    }

    private function saveFileDesign($data, $id, $url = 'design', $ext = 'pdf')
    {
        list($type, $data) = explode(';', $data);
        list($app, $extension) = explode('/', $type);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        Storage::put('public/' . $url . '/' . $id . '.' . $ext, $data);
    }

    protected function saveOrderInDB()
    {
        DB::beginTransaction();

        DB::rollBack();

        DB::commit();
    }


    private function generateTicket($order)
    {
        $pdf = Pdf::loadView('back.order.ticket', ['order' => $order]);
        $pdf->setPaper([0, 0, 210, 520]);

        Storage::put('public/tickets/' . $order->id . '.pdf', $pdf->output());

        return config('app.url') . Storage::url('public/tickets/' . $order->id . '.pdf');
    }

    protected function savePayment()
    {
    }
}
