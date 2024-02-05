<?php
namespace Tests\Traits;

use App\Models\Design;
use App\Models\Order;
use App\Models\OrderDesign;
use App\Models\OrderDetail;

trait OrderTrait {
    protected function createOrder()
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
        ]);

        $this->addDetailToOrder($order);

        return $order;
    }

    protected function addDetailToOrder(Order $order)
    {
        $detail = OrderDetail::factory()->create([
            'order_id' => $order->id,
        ]);

        $design = Design::factory()->create();

        OrderDesign::factory()->create([
            'order_detail_id' => $detail->id,
            'design_id' => $design->id,
        ]);
    }
}
