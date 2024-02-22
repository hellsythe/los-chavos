<?php

namespace App\Models;

use Sdkconsultoria\Core\Fields\TextField;
use Sdkconsultoria\Core\Models\Model as BaseModel;

class PlanningOrder extends BaseModel
{
    public const STATUS_PENDING = 200;
    public const STATUS_READY = 300;
    public const STATUS_MISSING = 400;

    protected $fillable = [
        'order_id',
        'planning_id',
        'status',
        'order',
        'deadline',
    ];

    protected function fields()
    {
        return[
            TextField::make('')->label('')->rules(['required']),
        ];
    }

    public function getTranslations() : array
    {
        return [
            'singular' => 'PlanningOrder',
            'plural' => 'PlanningOrder',
        ];
    }

    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    public function orderModel()
    {
        return Order::find($this->order_id);
    }

    public static function finishOrder($order)
    {
        $planningOrder = PlanningOrder::where('order_id', $order->id)->first();

        if (!$planningOrder) {
            return;
        }

        $planningOrder->status = PlanningOrder::STATUS_READY;
        $planningOrder->save();

        $planning = $planningOrder->planning;
        $planning->minutes_used += $order->minutes_total;
        $planning->save();
    }
}
