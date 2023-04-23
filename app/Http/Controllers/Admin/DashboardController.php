<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        return view('back.dashboard.index', [
            'order_missing_payment' => Order::where('status', Order::STATUS_MISSING_PAYMENT)->count(),
            'order_ready' => Order::where('status', Order::STATUS_READY)->count(),
            'order_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
        ]);
    }
}
