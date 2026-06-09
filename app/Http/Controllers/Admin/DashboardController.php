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
        $periodType = $request->input('period_type', 'school');
        $topLimit = (int) $request->input('top_limit', 10);
        $historyYears = (int) $request->input('history_years', 3);
        if (!in_array($topLimit, [10, 20, 30], true)) {
            $topLimit = 10;
        }
        if (!in_array($historyYears, [3, 4, 5], true)) {
            $historyYears = 3;
        }

        if ($periodType === 'school') {
            $now = Carbon::now();
            $currentYear = (int) $now->format('Y');
            $defaultSchoolYear = $now->lt(Carbon::create($currentYear, 8, 1)->startOfDay())
                ? $currentYear - 1
                : $currentYear;
            $baseYear = $selectedYear ?: $defaultSchoolYear;
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

        $demandForecast = $this->buildRangeForecast($start->copy(), $end->copy(), $request->input('branch_id'), $historyYears);

        $currentYear = (int) Carbon::now()->format('Y');
        $selectedPeriodYear = (int) $start->format('Y');
        $currentYearProjection = null;

        if ($selectedPeriodYear !== $currentYear) {
            $currentYearStart = $this->alignDateToYear($start->copy(), $currentYear)->startOfDay();
            $currentYearEnd = $this->alignDateToYear($end->copy(), $currentYear)->endOfDay();
            if ($currentYearEnd->lt($currentYearStart)) {
                $currentYearEnd = $currentYearStart->copy()->addDays(max(0, $start->diffInDays($end)))->endOfDay();
            }

            $currentYearProjection = $this->buildRangeForecast(
                $currentYearStart,
                $currentYearEnd,
                $request->input('branch_id'),
                $historyYears
            );
        }

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
            'history_years' => $historyYears,
            'chart_data' => $chartData,
            'popular_embroidery' => $popularEmbroidery,
            'demand_forecast' => $demandForecast,
            'current_year_projection' => $currentYearProjection,
            'branches' => \App\Models\Branch::query()->orderBy('name')->get(['id', 'name']),
            'available_years' => Order::query()
                ->select(DB::raw('YEAR(created_at) as year'))
                ->distinct()
                ->whereNotNull('created_at')
                ->orderByDesc('year')
                ->pluck('year'),
        ]));
    }

    protected function buildRangeForecast(Carbon $periodStart, Carbon $periodEnd, $branchId = null, int $historyYears = 3): array
    {
        $historicalPeriods = [];
        for ($i = $historyYears; $i >= 1; $i--) {
            $historicalPeriods[] = [
                'start' => $periodStart->copy()->subYear($i),
                'end' => $periodEnd->copy()->subYear($i),
            ];
        }

        $totalsByPeriod = [];
        foreach ($historicalPeriods as $index => $historicalPeriod) {
            $label = $historicalPeriod['start']->format('Y');
            $totalsByPeriod[$label] = Order::query()
                ->whereBetween('created_at', [$historicalPeriod['start'], $historicalPeriod['end']])
                ->when($branchId, function ($builder) use ($branchId) {
                    $builder->where('branch_id', $branchId);
                })
                ->count();
        }

        $recentGrowthRates = [];
        $sortedPeriodKeys = array_values(array_keys($totalsByPeriod));
        for ($i = 1; $i < count($sortedPeriodKeys); $i++) {
            $previous = $totalsByPeriod[$sortedPeriodKeys[$i - 1]] ?? 0;
            $current = $totalsByPeriod[$sortedPeriodKeys[$i]] ?? 0;
            if ($previous > 0) {
                $recentGrowthRates[] = ($current - $previous) / $previous;
            }
        }

        $weightedGrowth = 0.0;
        if (!empty($recentGrowthRates)) {
            $recentGrowthRates = array_reverse($recentGrowthRates);
            $numerator = 0.0;
            $denominator = 0.0;
            foreach ($recentGrowthRates as $index => $rate) {
                $weight = 1 / ($index + 1);
                $numerator += $rate * $weight;
                $denominator += $weight;
            }
            $weightedGrowth = $denominator > 0 ? $numerator / $denominator : 0.0;
        }

        $lastHistoricalKey = !empty($sortedPeriodKeys) ? end($sortedPeriodKeys) : null;
        $lastHistoricalTotal = $lastHistoricalKey ? ($totalsByPeriod[$lastHistoricalKey] ?? 0) : 0;
        $averageHistoricalTotal = count($totalsByPeriod) > 0
            ? array_sum($totalsByPeriod) / count($totalsByPeriod)
            : 0;

        $predictedTotal = $lastHistoricalTotal > 0
            ? (int) round($lastHistoricalTotal * (1 + $weightedGrowth))
            : (int) round($averageHistoricalTotal);
        $predictedTotal = max(0, $predictedTotal);

        $daysInPeriod = max(1, $periodStart->diffInDays($periodEnd) + 1);
        $dailyShares = array_fill(1, $daysInPeriod, 0.0);
        $validYearsForShares = 0;

        foreach ($historicalPeriods as $historicalPeriod) {
            $periodTotal = Order::query()
                ->whereBetween('created_at', [$historicalPeriod['start'], $historicalPeriod['end']])
                ->when($branchId, function ($builder) use ($branchId) {
                    $builder->where('branch_id', $branchId);
                })
                ->count();

            if ($periodTotal < 1) {
                continue;
            }

            $dailyRows = Order::query()
                ->whereBetween('created_at', [$historicalPeriod['start'], $historicalPeriod['end']])
                ->when($branchId, function ($builder) use ($branchId) {
                    $builder->where('branch_id', $branchId);
                })
                ->select([
                    DB::raw('DATE(created_at) as day_date'),
                    DB::raw('COUNT(*) as total_orders'),
                ])
                ->groupBy('day_date')
                ->orderBy('day_date')
                ->get();

            $sharesByDay = array_fill(1, $daysInPeriod, 0.0);
            foreach ($dailyRows as $row) {
                $dayIndex = Carbon::parse($row->day_date)->diffInDays($historicalPeriod['start']) + 1;
                if ($dayIndex >= 1 && $dayIndex <= $daysInPeriod) {
                    $sharesByDay[$dayIndex] = $row->total_orders / $periodTotal;
                }
            }

            for ($day = 1; $day <= $daysInPeriod; $day++) {
                $dailyShares[$day] += $sharesByDay[$day];
            }
            $validYearsForShares++;
        }

        if ($validYearsForShares > 0) {
            for ($day = 1; $day <= $daysInPeriod; $day++) {
                $dailyShares[$day] = $dailyShares[$day] / $validYearsForShares;
            }
        } else {
            for ($day = 1; $day <= $daysInPeriod; $day++) {
                $dailyShares[$day] = 1 / $daysInPeriod;
            }
        }

        $sumShares = array_sum($dailyShares);
        if ($sumShares <= 0) {
            $sumShares = 1;
        }
        for ($day = 1; $day <= $daysInPeriod; $day++) {
            $dailyShares[$day] = $dailyShares[$day] / $sumShares;
        }

        $actualDailyRows = Order::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->when($branchId, function ($builder) use ($branchId) {
                $builder->where('branch_id', $branchId);
            })
            ->select([
                DB::raw('DATE(created_at) as day_date'),
                DB::raw('COUNT(*) as total_orders'),
            ])
            ->groupBy('day_date')
            ->orderBy('day_date')
            ->get()
            ->keyBy('day_date');

        $predictedByDay = [];
        $runningPredicted = 0;
        $dayCandidates = [];
        for ($day = 1; $day <= $daysInPeriod; $day++) {
            $rawValue = $predictedTotal * $dailyShares[$day];
            $predicted = (int) floor($rawValue);
            $runningPredicted += $predicted;
            $dayCandidates[] = ['day' => $day, 'decimal' => $rawValue - $predicted];
            $predictedByDay[$day] = $predicted;
        }

        $missing = $predictedTotal - $runningPredicted;
        usort($dayCandidates, function ($a, $b) {
            return $b['decimal'] <=> $a['decimal'];
        });
        for ($i = 0; $i < $missing; $i++) {
            $targetDay = $dayCandidates[$i]['day'] ?? null;
            if ($targetDay) {
                $predictedByDay[$targetDay]++;
            }
        }

        $weeklyProjection = [];
        $actualToDate = 0;
        $predictedToDate = 0;
        $now = Carbon::now()->endOfDay();

        for ($day = 1; $day <= $daysInPeriod; $day++) {
            $date = $periodStart->copy()->addDays($day - 1);
            $dayKey = $date->format('Y-m-d');
            $weekNumber = (int) floor(($day - 1) / 7) + 1;
            $weekLabel = 'Semana ' . $weekNumber;

            if (!isset($weeklyProjection[$weekLabel])) {
                $weeklyProjection[$weekLabel] = [
                    'label' => $weekLabel,
                    'predicted_orders' => 0,
                    'actual_orders' => 0,
                ];
            }

            $predictedForDay = $predictedByDay[$day] ?? 0;
            $actualForDay = (int) ($actualDailyRows[$dayKey]->total_orders ?? 0);

            $weeklyProjection[$weekLabel]['predicted_orders'] += $predictedForDay;
            $weeklyProjection[$weekLabel]['actual_orders'] += $actualForDay;

            if ($date->lte($now)) {
                $actualToDate += $actualForDay;
                $predictedToDate += $predictedForDay;
            }
        }

        $mape = null;
        if (count($sortedPeriodKeys) >= 3) {
            $errors = [];
            for ($i = 2; $i < count($sortedPeriodKeys); $i++) {
                $first = $totalsByPeriod[$sortedPeriodKeys[$i - 2]] ?? 0;
                $second = $totalsByPeriod[$sortedPeriodKeys[$i - 1]] ?? 0;
                $actual = $totalsByPeriod[$sortedPeriodKeys[$i]] ?? 0;

                if ($first > 0 && $second > 0 && $actual > 0) {
                    $growth = ($second - $first) / $first;
                    $predicted = $second * (1 + $growth);
                    $errors[] = abs($actual - $predicted) / $actual;
                }
            }

            if (!empty($errors)) {
                $mape = round((array_sum($errors) / count($errors)) * 100, 2);
            }
        }

        return [
            'start' => $periodStart->format('Y-m-d'),
            'end' => $periodEnd->format('Y-m-d'),
            'year' => (int) $periodStart->format('Y'),
            'predicted_total_orders' => $predictedTotal,
            'actual_total_orders' => (int) array_sum(array_column($weeklyProjection, 'actual_orders')),
            'actual_to_date' => $actualToDate,
            'predicted_to_date' => $predictedToDate,
            'progress_percent' => $predictedToDate > 0 ? round(($actualToDate / $predictedToDate) * 100, 2) : null,
            'weighted_growth_percent' => round($weightedGrowth * 100, 2),
            'backtest_mape' => $mape,
            'history_years_used' => $historyYears,
            'historical_totals' => $totalsByPeriod,
            'weekly_projection' => array_values($weeklyProjection),
        ];
    }

    protected function alignDateToYear(Carbon $date, int $year): Carbon
    {
        $month = (int) $date->format('m');
        $day = (int) $date->format('d');
        $lastDay = Carbon::create($year, $month, 1)->endOfMonth()->day;

        return Carbon::create($year, $month, min($day, $lastDay));
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
