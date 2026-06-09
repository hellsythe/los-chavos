<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Design;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('back.dashboard.index',  array_merge($this->getMetrics(), [
            'missing_auth' => Order::where('status', Order::STATUS_WAITING_AUTH)->count(),
            'model' => new OrderDetail()
        ]));
    }

    public function orderStatistics(Request $request)
    {
        $selectedYear = $request->filled('year') ? (int) $request->input('year') : null;
        $periodType = $request->input('period_type', 'custom');
        $topLimit = (int) $request->input('top_limit', 10);
        if (!in_array($topLimit, [10, 20, 30], true)) {
            $topLimit = 10;
        }

        if ($periodType === 'school') {
            $baseYear = $selectedYear ?: (int) Carbon::now()->format('Y');
            $start = Carbon::create($baseYear, 8, 1)->startOfDay();
            $end = Carbon::create($baseYear, 9, 30)->endOfDay();
        } elseif ($periodType === 'yearly') {
            $baseYear = $selectedYear ?: (int) Carbon::now()->format('Y');
            $start = Carbon::create($baseYear, 1, 1)->startOfDay();
            $end = Carbon::create($baseYear, 12, 31)->endOfDay();
        } else {
            $start = $request->filled('start')
                ? Carbon::parse($request->input('start'))->startOfDay()
                : Carbon::now()->subYears(3)->startOfMonth();

            $end = $request->filled('end')
                ? Carbon::parse($request->input('end'))->endOfDay()
                : Carbon::now()->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        if (in_array($periodType, ['yearly', 'school'], true) && !$selectedYear) {
            $selectedYear = (int) $start->format('Y');
        }

        $query = Order::query()->whereBetween('created_at', [$start, $end]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $totalOrders = (clone $query)->count();

        $days = max(1, $start->diffInDays($end) + 1);
        $averageOrdersPerDay = round($totalOrders / $days, 2);

        $previousStart = $start->copy()->subYear();
        $previousEnd = $end->copy()->subYear();

        $previousQuery = Order::query()->whereBetween('created_at', [$previousStart, $previousEnd]);
        if ($request->filled('branch_id')) {
            $previousQuery->where('branch_id', $request->input('branch_id'));
        }

        $previousTotalOrders = $previousQuery->count();
        $variationPercent = $previousTotalOrders > 0
            ? round((($totalOrders - $previousTotalOrders) / $previousTotalOrders) * 100, 2)
            : null;

        $monthlyOrders = (clone $query)
            ->select([
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
                DB::raw('COUNT(*) as total_orders'),
            ])
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $chartData = [];
        $cursor = $start->copy()->startOfMonth();
        $limit = $end->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $period = $cursor->format('Y-m');
            $chartData[] = [
                'period' => $cursor->translatedFormat('M Y'),
                'period_key' => $period,
                'total_orders' => (int) ($monthlyOrders[$period]->total_orders ?? 0),
            ];

            $cursor->addMonth();
        }

        $popularEmbroideryExisting = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('order_designs as odg', 'odg.order_detail_id', '=', 'od.id')
            ->join('designs as d', 'd.id', '=', 'odg.design_id')
            ->where('od.service_id', 1)
            ->whereBetween('o.created_at', [$start, $end])
            ->when($request->filled('branch_id'), function ($builder) use ($request) {
                $builder->where('o.branch_id', $request->input('branch_id'));
            })
            ->select([
                DB::raw('d.name as design_name'),
                DB::raw('od.order_id as order_id'),
                DB::raw('od.garment_amount as garment_amount'),
            ]);

        $popularEmbroideryNew = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('order_new_designs as ond', 'ond.order_detail_id', '=', 'od.id')
            ->join('designs as d', 'd.id', '=', 'ond.design_id')
            ->where('od.service_id', 1)
            ->whereBetween('o.created_at', [$start, $end])
            ->when($request->filled('branch_id'), function ($builder) use ($request) {
                $builder->where('o.branch_id', $request->input('branch_id'));
            })
            ->select([
                DB::raw('d.name as design_name'),
                DB::raw('od.order_id as order_id'),
                DB::raw('od.garment_amount as garment_amount'),
            ]);

        $popularEmbroideryUpdated = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('order_update_designs as oud', 'oud.order_detail_id', '=', 'od.id')
            ->join('designs as d', 'd.id', '=', 'oud.design_id')
            ->where('od.service_id', 1)
            ->whereBetween('o.created_at', [$start, $end])
            ->when($request->filled('branch_id'), function ($builder) use ($request) {
                $builder->where('o.branch_id', $request->input('branch_id'));
            })
            ->select([
                DB::raw('d.name as design_name'),
                DB::raw('od.order_id as order_id'),
                DB::raw('od.garment_amount as garment_amount'),
            ]);

        $popularEmbroidery = DB::query()
            ->fromSub(
                $popularEmbroideryExisting->unionAll($popularEmbroideryNew)->unionAll($popularEmbroideryUpdated),
                'embroidery_designs'
            )
            ->select([
                'design_name',
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                DB::raw('SUM(garment_amount) as total_garments'),
            ])
            ->groupBy('design_name')
            ->orderByDesc('total_orders')
            ->limit($topLimit)
            ->get();

        return view('back.dashboard.order-statistics', array_merge($this->getMetrics(), [
            'total_orders' => $totalOrders,
            'previous_total_orders' => $previousTotalOrders,
            'variation_percent' => $variationPercent,
            'average_orders_per_day' => $averageOrdersPerDay,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'branch_id' => $request->input('branch_id'),
            'selected_year' => $selectedYear,
            'period_type' => $periodType,
            'top_limit' => $topLimit,
            'chart_data' => $chartData,
            'popular_embroidery' => $popularEmbroidery,
            'branches' => \App\Models\Branch::query()->orderBy('name')->get(['id', 'name']),
            'available_years' => Order::query()
                ->select(DB::raw('YEAR(created_at) as year'))
                ->distinct()
                ->whereNotNull('created_at')
                ->orderByDesc('year')
                ->pluck('year'),
        ]));
    }

    public function indexGrupBy(Request $request)
    {
        if (auth()->user()->hasRole('Estampador')) {
            $data = $this->groupDesignPrint();
        }
        if (auth()->user()->hasRole('Bordador')) {
            $data = $this->groupDesign();
        }

        if (auth()->user()->hasRole('super-admin')) {
            if ($request->input('type') == 'print') {
                $data = $this->groupDesignPrint();
            } else {
                $data = $this->groupDesign();
            }
        }

        $data = $data
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->where('orders.status', Order::STATUS_PENDING)
        ->orderBy('garment', 'DESC');

        if (auth()->user()->hasRole('Bordador') && $request->start && $request->end){
            $data = $data->whereBetween('deadline', [$request->start, $request->end]);
        }

        if ($request->name){
            $data = $data->where('designs.name', 'like', '%'.$request->name.'%');
        }


        $data = $data->get();

        return view('back.dashboard.index-groupby', array_merge($this->getMetrics(), [
            'model' => new OrderDetail(),
            'data' => $data
        ]));
    }

    protected function groupDesignPrint()
    {
        return OrderDetail::select([
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(order_details.garment_amount) as garment'),
            'design_prints.name as design',
            'design_prints.id as desing_id',
        ])
        ->join('order_design_prints', 'order_design_prints.order_detail_id', '=', 'order_details.id')
        ->groupBy('design_print_id')
        ->join('design_prints', 'order_design_prints.design_print_id', '=', 'design_prints.id');
    }

    protected function groupDesign()
    {
        return OrderDetail::select([
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(order_details.garment_amount) as garment'),
            'designs.name as design',
            'designs.id as desing_id',
        ])
        ->join('order_designs', 'order_designs.order_detail_id', '=', 'order_details.id')
        ->groupBy('design_id')
        ->join('designs', 'order_designs.design_id', '=', 'designs.id');
    }

    public function ordersGroupBy($id)
    {

        $data = OrderDetail::select([
            'orders.id',
        ])
        ->join('order_designs', 'order_designs.order_detail_id', '=', 'order_details.id')
        ->join('designs', 'order_designs.design_id', '=', 'designs.id')
        ->join('orders', 'orders.id', '=', 'order_details.order_id');

        $data = $data->where('orders.status', Order::STATUS_PENDING)
        ->where('designs.id', $id)
        ->get()->pluck('id')->toArray();

        $orders = Order::whereIn('id', $data)->with('client')->get();

        return view('back.dashboard.group', [
            'model' => Design::findModel($id),
            'models' => $orders
        ]);
    }

    protected function filterByRol(&$data)
    {
        if (auth()->user()->hasRole('Bordador')) {
            $data = $data->where('order_details.service_id', 1);
        }

        if (auth()->user()->hasRole('Estampador')) {
            $data = $data->where('order_details.service_id', 2);
        }
    }

    protected function getMetrics()
    {
        return [
            'order_missing_payment' => Order::where('status', Order::STATUS_MISSING_PAYMENT)->count(),
            'order_ready' => Order::where('status', Order::STATUS_READY)->count(),
            'order_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'missing_auth' => Order::where('status', Order::STATUS_WAITING_AUTH)->count(),
        ];
    }
}
