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

        if($planningDate == $today)
        {
            $lastPlanning->addOrder($order);
        } else {
            $position = $this->findPositionInPlaning($order, $lastPlanning);
            $response = $this->findPositionFromDate($order, $lastPlanning, $position);
            $current = $response['planning']->addOrder($order);
            $response['planning']->reorder($current, $response['position']);
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

            break;
        }

        return [
            'planning' => $oldPlanning,
            'position' => $oldPosition,
        ];
    }
}
