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
        $orders = PlanningOrder::whereIn('planning_id', $plannings)->get();
        $events = [];

        foreach ($orders as $planningOrder) {
            $events[] = [
                'title' => $planningOrder->orderModel()->deadlinex . ' ' .$planningOrder->orderModel()->desings,
                'start' => $planningOrder->planning->date->format('Y-m-d'),
                'end' => $planningOrder->planning->date->format('Y-m-d'),
                'description' => 'Lecture',
            ];
        }

        return $events;
    }
}
