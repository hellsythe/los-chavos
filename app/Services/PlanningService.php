<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Planning;
use App\Models\PlanningOrder;
use App\Services\Planning\GetLastPlanningAvailable;

class PlanningService
{
    public function addOrder(Order $order): void
    {
        $planning = resolve(GetLastPlanningAvailable::class);
        // $planning = $planning->get($order)->addOrder($order);

        $planning = $planning->get($order); //->addOrder($order)
        $this->addToQueue($order, $planning);
    }

    private function addToQueue(Order $order, Planning $lastPlanning): void
    {
        $planningDate = $lastPlanning->date->format('Y-m-d');
        $today = date('Y-m-d');
        $yesterdayDate = date('Y-m-d', strtotime( $planningDate . ' - 1 days'));

        if($planningDate == $today || $yesterdayDate == $today)
        {
            $lastPlanning->addOrder($order);
        } else {
            $position = $this->findPositionInPlaning($order, $lastPlanning);
            // $this->findPositionFromDate($order, $lastPlanning);
            $current = $lastPlanning->addOrder($order);
            $lastPlanning->reorder($current, $position);
            // $this->checkIfCanAddToOldsPlannings($current);
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

    private function findPositionFromDate(Order $order, $planning)
    {
        $oldPlanning = $planning;
        $date = date('Y-m-d', strtotime( $planning->date->format('Y-m-d') . ' - 1 days'));
        $oldPosition = 0;
        $currentPosition = 0;

        while (true) {
            if ($date == date('Y-m-d')) {
                return 0;
            }

            $currentPlanning = Planning::where('date', $date)->first();

            if (!$currentPlanning) {
                return 0;
            }

            $currentPosition = $this->findPositionInPlaning($order, $currentPlanning);

            if ($currentPosition == $currentPlanning->orders->count()) {
                return $oldPosition;
            }


            break;
        }
    }
}
