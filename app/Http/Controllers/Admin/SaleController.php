<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class SaleController extends ResourceController
{
    protected $model = \App\Models\Sale::class;

    public function salePoint()
    {
        return view('back.sale.point');
    }
}
