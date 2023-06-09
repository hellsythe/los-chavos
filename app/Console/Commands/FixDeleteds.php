<?php

namespace App\Console\Commands;

use App\Models\OrderDetail;
use Illuminate\Console\Command;

class FixDeleteds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-deleteds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ordes_details = OrderDetail::select('order_details.*')->join('orders', 'orders.id', '=', 'order_details.order_id')->whereNotNull('orders.deleted_at')->get();

        foreach ($ordes_details as $order) {
            $order->delete();
        }
    }
}
