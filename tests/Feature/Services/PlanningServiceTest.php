<?php

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\Planning;
use App\Services\PlanningService;
use Tests\TestCase;
use Tests\Traits\OrderTrait;
use Mockery\MockInterface;

class PlanningServiceTest extends TestCase
{
    use OrderTrait;

    public function test_adding_a_order_to_new_planning(): void
    {
        Planning::truncate();

        $this->partialMock(PlanningService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAverageForDay')->once()->andReturn(10000);
        });

        $order = $this->createOrder();
        resolve(PlanningService::class)->addOrder($order);

        $this->assertDatabaseCount('plannings', 1);
        $this->assertDatabaseHas('plannings', [
            'minutes_available' => 10000 - $order->minutes_total,
            'minutes_max' => 10000,
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'minutes_scheduled' => $order->minutes_total,
            'status' => Planning::STATUS_ACTIVE,
        ]);

    }

    public function test_adding_two_order_to_planing(): void
    {
        Planning::truncate();

        $this->partialMock(PlanningService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAverageForDay')->once()->andReturn(10000);
        });

        $order_a = $this->createOrder();;
        $order_b = $this->createOrder();;
        $planning = resolve(PlanningService::class);
        $planning->addOrder($order_a);
        $planning->addOrder($order_b);

        $this->assertDatabaseCount('plannings', 1);
        $this->assertDatabaseHas('plannings', [
            'minutes_available' => 10000 - $order_a->minutes_total - $order_b->minutes_total,
            'minutes_max' => 10000,
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'minutes_scheduled' => $order_a->minutes_total + $order_b->minutes_total,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    public function test_adding_order_to_planing_full(): void
    {
        Planning::truncate();

        Planning::create([
            'date' => date('Y-m-d'),
            'minutes_available' => 10,
            'minutes_max' => 10000,
            'minutes_missing' => 0,
            'minutes_used' => 9990,
            'minutes_scheduled' => 9990,
            'status' => Planning::STATUS_ACTIVE,
        ]);

        $this->partialMock(PlanningService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAverageForDay')->once()->andReturn(10000);
        });

        $order = $this->createOrder();
        resolve(PlanningService::class)->addOrder($order);

        $this->assertDatabaseCount('plannings', 2);
        $this->assertDatabaseHas('plannings', [
            'minutes_available' => 10000 - $order->minutes_total,
            'minutes_max' => 10000,
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'minutes_scheduled' => $order->minutes_total,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    public function test_calculating_average(): void
    {
        Planning::truncate();

        $order = $this->createOrder();
        resolve(PlanningService::class)->addOrder($order);

        $this->assertDatabaseCount('plannings', 1);
    }
}
