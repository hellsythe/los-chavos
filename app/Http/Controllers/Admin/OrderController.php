<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class OrderController extends ResourceController
{
    protected $model = \App\Models\Order::class;
}
