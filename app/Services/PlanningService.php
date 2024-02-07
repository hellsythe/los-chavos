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
        // $planning = $planning->get($order)->addOrder($order);

        $planning = $planning->get($order); //->addOrder($order)
        $this->addToQueue($order, $planning);
    }

    private function addToQueue(Order $order, Planning $lastPlanning): void
    {
        $planningDate = $lastPlanning->date->format('Y-m-d');
        $today = date('Y-m-d');
        $newDate = date('Y-m-d', strtotime( $planningDate . ' - 1 days'));

        if($planningDate == $today || $newDate == $today)
        {
            $lastPlanning->addOrder($order);
        } else {
            $this->findPositionInPlaning($order, $lastPlanning);
        }

    }

    private function findPositionInPlaning(Order $order, Planning $planning)
    {
        if ($planning->orders->isEmpty()) {
            $planning->addOrder($order);
            return;
        }

        foreach ($planning->orders as $orderInArray) {
            $date = $order->getAttribute('deadlinex');
            if ($date < $orderInArray->deadline){
                $current = $planning->addOrder($order);
                $planning->reorder($current, $orderInArray->order);
                return;
            }
        }

        $planning->addOrder($order);
    }
}
