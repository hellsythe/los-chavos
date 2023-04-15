<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use App\Models\Service;

class OrderController extends ResourceController
{
    protected $model = \App\Models\Order::class;

    public function salePoint()
    {
        return view('back.sale.point', [
            'available_services' => Service::all()
        ]);
    }
}
