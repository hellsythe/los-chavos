<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Planning;

class PlanningService
{
    protected Order $order;

    public function addOrder(Order $order): void
    {
        $this->order = $order;
        $planning = $this->getLastPlanningAvailable();
        dd($planning);
    }

    protected function getLastPlanningAvailable(): Planning
    {
        $planning = Planning::where('date', '>=', date('Y-m-d'))->where('minutes_available', '>' ,0)->first();

        if ($planning) {
            return $planning;
        }

        return $this->createNewPlanning();
    }

    protected function createNewPlanning(): Planning
    {
        $planning = Planning::orderBy('date', 'desc')->first();

        if ($planning) {
            return $this->createPlanning($planning->date->addDay());
        }

        return $this->createPlanning(date('Y-m-d'));
    }

    protected function createPlanning($date): Planning
    {
        return Planning::create([
            'date' => $date,
            'minutes_available' => $this->getMinutesAvailable(),
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    protected function getMinutesAvailable(): int
    {
        return 1000;
    }
}
