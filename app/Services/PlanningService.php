<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Planning;
use App\Services\Planning\GetLastPlanningAvailable;

class PlanningService
{
    public function addOrder(Order $order): void
    {
        $planning = resolve(GetLastPlanningAvailable::class);
        $planning = $planning->get($order);
        $this->addToQueue($order, $planning);
    }

    private function addToQueue(Order $order, Planning $lastPlanning): void
    {
        if($lastPlanning->date->format('Y-m-d') == date('Y-m-d'))
        {
            $lastPlanning->addOrder($order);
        } else {
            $position = $this->findPositionInPlaning($order, $lastPlanning);
            $response = $this->findPositionFromDate($order, $lastPlanning, $position);
            $current = $response['planning']->addOrder($order);
            $response['planning']->reorder($current, $response['position']);
            $this->reorderPositionsIfNecessary($response['planning'], $response['counter']);
        }

    }

    private function findPositionInPlaning(Order $order, Planning $planning): int
    {
        if ($planning->orders->isEmpty()) {
            return 0;
        }

        $position = count($planning->orders) + 1;

        foreach ($planning->orders as $orderInArray) {
            $date = $order->getAttribute('deadlinex');
            if ($date < $orderInArray->deadline){
                $position = $orderInArray->order;
            }
        }

        return $position;
    }

    private function findPositionFromDate(Order $order, $planning, $position)
    {
        $counter = 0;
        $oldPlanning = $planning;
        $oldPosition = $position;
        $date = date('Y-m-d', strtotime( $planning->date->format('Y-m-d') . ' - 1 days'));

        while (true) {
            if ($date == date('Y-m-d')) {
                break;
            }

            $currentPlanning = Planning::where('date', $date)->first();

            if (!$currentPlanning) {
                break;
            }

            $currentPosition = $this->findPositionInPlaning($order, $currentPlanning);

            if ($currentPosition == ($currentPlanning->orders->count() + 1)) {
                break;
            }

            $oldPlanning = $currentPlanning;
            $oldPosition = $currentPosition;
            $counter++;

            break;
        }

        return [
            'planning' => $oldPlanning,
            'position' => $oldPosition,
            'counter' => $counter,
        ];
    }

    private function reorderPositionsIfNecessary($planning, $counter)
    {
        if ($counter == 0) {
            return;
        }

        for($i = 0; $i < $counter; $i++) {
            $nextPlaning = Planning::where('date', date('Y-m-d', strtotime($planning->date->format('Y-m-d') . ' + 1 days')))->first();
            $order = $planning->removeLastOrder();
            $planningOrder = $nextPlaning->addOrder($order);
            $nextPlaning->reorder($planningOrder, 1);
            $planning = $nextPlaning;
        }
    }
}
