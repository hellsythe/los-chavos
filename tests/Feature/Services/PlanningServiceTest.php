<?php

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\Planning;
use App\Models\PlanningOrder;
use App\Services\Planning\GetLastPlanningAvailable;
use App\Services\PlanningService;
use Tests\TestCase;
use Tests\Traits\OrderTrait;
use Mockery\MockInterface;

class PlanningServiceTest extends TestCase
{
    use OrderTrait;

    public function test_adding_a_order_to_new_planning(): void
    {
        PlanningOrder::truncate();
        Planning::truncate();

        $this->partialMock(GetLastPlanningAvailable::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAverageForDay')->once()->andReturn(10000);
        });

        $order = $this->createOrder();
        $planning = resolve(PlanningService::class);
        $planning->addOrder($order);

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
        PlanningOrder::truncate();
        Planning::truncate();
        // $m = \Mockery::mock(ScanEpisode::class, [ 'episode' => 1])->makePartial();

        $this->partialMock(GetLastPlanningAvailable::class, function (MockInterface $mock) {
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
        PlanningOrder::truncate();
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

        $this->partialMock(GetLastPlanningAvailable::class, function (MockInterface $mock) {
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
        PlanningOrder::truncate();
        Planning::truncate();

        $order = $this->createOrder();
        resolve(PlanningService::class)->addOrder($order);
        $this->assertDatabaseCount('plannings', 1);
    }

    public function test_add_order_and_by_date()
    {
        PlanningOrder::truncate();
        Planning::truncate();

        $this->createPlaningFull(date('Y-m-d'));
        $this->createPlaningEmpty(date('Y-m-d', strtotime(' + 1 days')));

        $orderA = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 6 days'))]);
        $orderB = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 8 days'))]);
        $orderC = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 9 days'))]);
        $orderD = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 7 days'))]);

        $planning = resolve(PlanningService::class);
        $planning->addOrder($orderA);
        $planning->addOrder($orderB);
        $planning->addOrder($orderC);
        $planning->addOrder($orderD);

        $this->assertDatabaseCount('plannings', 2);
        $this->ensurePlaningOrder($orderA, 1);
        $this->ensurePlaningOrder($orderB, 3);
        $this->ensurePlaningOrder($orderC, 4);
        $this->ensurePlaningOrder($orderD, 2);
    }

    private function createPlaningFull($date)
    {
        return Planning::create([
            'date' => $date,
            'minutes_available' => 1,
            'minutes_max' => 10000,
            'minutes_missing' => 0,
            'minutes_used' => 9999,
            'minutes_scheduled' => 9999,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    private function createPlaningEmpty($date)
    {
        return Planning::create([
            'date' => $date,
            'minutes_available' => 200000,
            'minutes_max' => 200000,
            'minutes_missing' => 0,
            'minutes_used' => 0,
            'minutes_scheduled' => 0,
            'status' => Planning::STATUS_ACTIVE,
        ]);
    }

    private function ensurePlaningOrder($order, $position)
    {
        $this->assertDatabaseHas('planning_orders', [
            'order_id' => $order->id,
            'order' => $position,
        ]);
    }

    public function test_add_order_and_by_date_two_plannings_first_with_no_space_available()
    {
        PlanningOrder::truncate();
        Planning::truncate();

        $this->createPlaningFull(date('Y-m-d'));
        $planningFull = $this->createPlaningEmpty(date('Y-m-d', strtotime(' + 1 days')));

        $orderA = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 6 days'))]);
        $orderB = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 8 days'))]);
        $orderC = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 9 days'))]);
        $orderD = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 7 days'))]);

        $planning = resolve(PlanningService::class);
        $planning->addOrder($orderA);
        $planning->addOrder($orderB);
        $planning->addOrder($orderC);
        $planning->addOrder($orderD);

        $planningFull->refresh();
        $planningFull->minutes_available = 0;
        $planningFull->minutes_max = $planningFull->minutes_scheduled;
        $planningFull->minutes_used = $planningFull->minutes_scheduled;
        $planningFull->save();

        $orderA1 = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 10 days'))]);
        $orderB2 = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 6 days'))]);
        $orderC3 = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 5 days'))]);
        $orderD4 = $this->createOrder(['deadline' => date('Y-m-d', strtotime(' + 4 days'))]);

        $planning = resolve(PlanningService::class);
        $planning->addOrder($orderA1);
        $planning->addOrder($orderB2);
        $planning->addOrder($orderC3);
        $planning->addOrder($orderD4);

        $planning = resolve(GetLastPlanningAvailable::class);
        $planning = $planning->get($orderA1);

        $this->assertDatabaseCount('plannings', 3);

        $this->ensurePlaningOrderFull($orderD4, 1, $planningFull);
        $this->ensurePlaningOrderFull($orderC3, 2, $planningFull);
        $this->ensurePlaningOrderFull($orderA, 3, $planningFull);
        $this->ensurePlaningOrderFull($orderB2, 4, $planningFull);

        $this->ensurePlaningOrderFull($orderD, 1, $planning);
        $this->ensurePlaningOrderFull($orderB, 2, $planning);
        $this->ensurePlaningOrderFull($orderC, 3, $planning);
        $this->ensurePlaningOrderFull($orderA1, 4, $planning);
    }


    private function ensurePlaningOrderFull($order, $position, $planning)
    {
        $this->assertDatabaseHas('planning_orders', [
            'order_id' => $order->id,
            'order' => $position,
            'planning_id' => $planning->id,
        ]);
    }

}
