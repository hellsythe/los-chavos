<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class MigrateServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convierte servicios antiguos al nuevo modelo de servicios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            if ($order->garment) {
                foreach ($order->services as $service) {
                    if (!$service->garment_id && !$service->garment_amount) {
                        $service->garment_id = $order->garment_id;
                        $service->garment_amount = $order->garment_amount;
                        $service->save();
                    }
                }
            }
        }
    }
}
