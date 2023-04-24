<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Design;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('back.dashboard.index', [
            'order_missing_payment' => Order::where('status', Order::STATUS_MISSING_PAYMENT)->count(),
            'order_ready' => Order::where('status', Order::STATUS_READY)->count(),
            'order_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'model' => new OrderDetail()
        ]);
    }

    public function indexGrupBy()
    {
        $data = OrderDetail::select([
            DB::raw('COUNT(*) as total'),
            'designs.name as design',
            'designs.id as desing_id',
        ])
        ->join('order_designs', 'order_designs.order_detail_id', '=', 'order_details.id')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->join('designs', 'order_designs.design_id', '=', 'designs.id')
        ->where('orders.status', Order::STATUS_PENDING)
        ->groupBy('order_designs.design_id')
        ->orderBy('total', 'DESC')
        ->get();

        return view('back.dashboard.index-groupby', [
            'order_missing_payment' => Order::where('status', Order::STATUS_MISSING_PAYMENT)->count(),
            'order_ready' => Order::where('status', Order::STATUS_READY)->count(),
            'order_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'model' => new OrderDetail(),
            'data' => $data
        ]);
    }

    public function ordersGroupBy($id)
    {

        $data = OrderDetail::select([
            'orders.id',
        ])
        ->join('order_designs', 'order_designs.order_detail_id', '=', 'order_details.id')
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->join('designs', 'order_designs.design_id', '=', 'designs.id')
        ->where('orders.status', Order::STATUS_PENDING)
        ->where('designs.id', $id)
        ->get()->pluck('id')->toArray();

        $orders = Order::whereIn('id', $data)->with('client')->get();

        return view('back.dashboard.group', [
            'model' => Design::findModel($id),
            'models' => $orders
        ]);
    }
}
