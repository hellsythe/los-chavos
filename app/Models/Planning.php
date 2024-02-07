<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class Planning extends BaseModel
{
    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'date',
        'minutes_available',
        'minutes_missing',
        'minutes_used',
        'status',
        'minutes_max',
        'minutes_scheduled',
    ];

    protected function fields()
    {
        return[
            TextField::make('date')->label('Fecha')->rules(['required']),
            TextField::make('minutes_available')->label('Puntadas Disponibles')->rules(['required']),
            TextField::make('minutes_used')->label('Puntadas Gastadas')->rules(['required']),
            TextField::make('minutes_missing')->label('Puntadas Sin Terminar')->rules(['required']),
            TextField::make('minutes_max')->label('Puntadas Maximas')->rules(['required']),
            TextField::make('minutes_scheduled')->label('Puntadas Programadas')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'Planificación',
            'plural' => 'Planificación',
        ];
    }

    public function addOrder(Order $order): PlanningOrder
    {
        $this->minutes_available -= $order->minutes_total;
        $this->minutes_scheduled += $order->minutes_total;
        $this->save();

        return PlanningOrder::create([
            'order_id' => $order->id,
            'planning_id' => $this->id,
            'status' => PlanningOrder::STATUS_PENDING,
            'order' => (PlanningOrder::where('planning_id', $this->id)->count() + 1),
            'deadline' => $order->getRawOriginal('deadline'),
        ]);
    }

    public function orders()
    {
        return $this->hasMany(PlanningOrder::class, 'planning_id', 'id')->orderBy('order', 'desc');
    }

    public function Reorder(PlanningOrder $current, int $newOrder)
    {
        if ($current->order == $newOrder || $newOrder == 0) {
            return;
        }

        $current->order = $newOrder;
        $current->save();

        $ordersToReorder = $this->orders()->where('order', '>=', $newOrder)->where('id', '!=', $current->id)->get();
        foreach ($ordersToReorder as $order) {
            $order->order++;
            $order->save();
        }
    }

    public static function removeOrder(Order $order)
    {
        $planningOrder = PlanningOrder::where('order_id', $order->id)->first();
        // $this->minutes_available -= $order->minutes_total;
        // $this->minutes_scheduled += $order->minutes_total;
        // $this->save();

        // return PlanningOrder::create([
        //     'order_id' => $order->id,
        //     'planning_id' => $this->id,
        //     'status' => PlanningOrder::STATUS_PENDING,
        //     'order' => (PlanningOrder::where('planning_id', $this->id)->count() + 1),
        //     'deadline' => $order->getRawOriginal('deadline'),
        // ]);
    }
}
