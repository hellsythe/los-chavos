<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Planning;
use App\Models\PlanningOrder;
use App\Services\PlanningService;
use Illuminate\Console\Command;

class ReorderPlannings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reorder-plannings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borra y reorganiza todos los trabajos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PlanningOrder::truncate();
        Planning::truncate();

        $orders = Order::where('deadline', '>=', date('Y-m-d'))
            ->where('status', Order::STATUS_PENDING)
            ->orderBy('deadline')
            ->get();

            foreach ($orders as $order) {
            $planning = resolve(PlanningService::class);
            $planning->addOrder($order);
        }
    }
}
