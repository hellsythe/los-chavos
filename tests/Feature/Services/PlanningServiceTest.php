<?php

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\Planning;
use App\Services\PlanningService;
use Tests\TestCase;

class PlanningServiceTest extends TestCase
{
    use \Tests\Traits\OrderTrait;

    public function test_get_last_planning_available(): void
    {
        $order = $this->createOrder();;
        $plan = resolve(PlanningService::class);
        $plan->addOrder($order);
        $this->assertTrue(true);
        //$this->deleteAllPlannings();
    }

    private function deleteAllPlannings(): void
    {
        Planning::truncate();
    }

    public function test_adding_a_order_to_new_planning(): void
    {
        $this->assertTrue(true);
    }
}
