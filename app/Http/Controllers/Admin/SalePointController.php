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
                'client' => [
                    'name' => '',
                    'phone' => '',
                    'email' => '',
                ],
                'data' => [
                    'id' => '',
                    'deadline' => '',
                    'created_at' => '',
                ],
                'services' => [
                    []
                ],
                'payments' => [

                ],
                'detail' => [
                    'deadline' => ''
                ],
            ],
        ]);
    }

    public function salePointEdit($id)
    {
        $this->authorize('create', new Order());
        $model = Order::with('client')->with('details')->find($id)->toArray();

        return view('back.sale.edit', [
            'available_services' => Service::all(),
            'model' => $model
        ]);
    }
}
