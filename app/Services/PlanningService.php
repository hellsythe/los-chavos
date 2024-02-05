<?php
namespace App\Services;

use App\Models\EmbroideryStatistics;
use App\Models\Order;
use App\Models\Planning;
use Carbon\Carbon;

class PlanningService
{
    protected Order $order;
    protected Planning $planning;

    public function addOrder(Order $order): void
    {
        $this->order = $order;
        $planning = $this->getLastPlanningAvailable();
        $planning->addOrder($order);
    }

    protected function getLastPlanningAvailable(): Planning
    {
        $planning = Planning::where('date', '>=', date('Y-m-d'))->where('minutes_available', '>' ,0)->first();

        if ($planning) {
            if ($planning->minutes_available < $this->order->minutes_total) {
                return $this->createNewPlanning();
            }
            return $planning;
        }

        return $this->createNewPlanning();
    }

    protected function createNewPlanning(): Planning
    {
        $planning = Planning::latest('date')->first();

        if ($planning) {
            return $this->createPlanning($planning->date->addDay());
        }

        return $this->createPlanning(date('Y-m-d'));
    }

    protected function createPlanning($date): Planning
    {
        $minutes_average = $this->getAverageForDay($date);

        return Planning::create([
            'date' => $date,
            'minutes_available' => $minutes_average,
            'minutes_max' => $minutes_average,
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'minutes_scheduled' => 0,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    public function getAverageForDay(string $date): int
    {
        $initial_date = new Carbon($date);
        $initial_date = $initial_date->subDays(10)->toDateString();
        return (int) EmbroideryStatistics::where('date', '>', $initial_date)->avg('minutes');
    }
}
