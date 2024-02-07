<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\PlanningOrder;

class CalendarController extends Controller
{
    public function index()
    {
        return view('back.calendar.index', [
            'events' => $this->getEvents()
        ]);
    }

    private function getEvents(): array
    {
        $plannings = Planning::where('date' , '>=', date('Y-m-d'))->pluck('id')->toArray();
        $orders = PlanningOrder::whereIn('planning_id', $plannings)
            ->where('status', PlanningOrder::STATUS_PENDING)
            ->get();
        $events = [];

        foreach ($orders as $planningOrder) {
            $model = $planningOrder->orderModel();
            $events[] = [
                'title' => $model->deadlinex . ' ' .$model->desings,
                'start' => $planningOrder->planning->date->format('Y-m-d'),
                'end' => $planningOrder->planning->date->format('Y-m-d'),
                'description' => 'Lecture',
                'extendedProps' => [
                    'model_id' => $model->id,
                ],
                'url' => route('order.view', $model->id),
            ];
        }

        return $events;
    }
}
