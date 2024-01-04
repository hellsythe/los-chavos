<?php

namespace Tests\Feature\Services;

use App\Models\Design;
use App\Models\Order;
use App\Models\OrderDesign;
use App\Models\OrderDetail;
use App\Services\PlanningService;
use Tests\TestCase;

class PlanningServiceTest extends TestCase
{
    public function test_get_last_planning_available(): void
    {
        $order = $this->createOrderReadyToBePlanned();
        $plan = resolve(PlanningService::class);
        $plan->addOrder($order);
        $this->assertTrue(true);
    }

    public function test_adding_a_order_to_new_planning(): void
    {
        $this->assertTrue(true);
    }

    private function createOrderReadyToBePlanned(): Order
    {
        return $this->createOrder();
        return Order::where('status', Order::STATUS_PENDING)->first();
    }

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
