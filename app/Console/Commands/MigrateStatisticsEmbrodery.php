<?php

namespace App\Console\Commands;

use App\Models\EmbroideryStatistics;
use App\Models\Order;
use Illuminate\Console\Command;

class MigrateStatisticsEmbrodery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-statistics-embrodery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar las estadisticas de bordado a la nueva estructura';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->migrateFromStatusReadyToFinishedAt();
        $this->migrateFromStatusFinishToFinishedAt();

        $this->info('Estadisticas de bordado migradas correctamente');
    }

    private function migrateFromStatusReadyToFinishedAt()
    {
        $orders = Order::where('status', Order::STATUS_READY)->get();

        foreach ($orders as $order) {
            $order->finished_at = $order->updated_at;
            $order->save();
        }
    }
    private function migrateFromStatusFinishToFinishedAt()
    {
        $orders = Order::where('status', Order::STATUS_FINISH)->get();

        foreach ($orders as $order) {
            $order->finished_at = $order->updated_at;
            $order->delivery_at = $order->updated_at;
            $order->save();
        }
    }
}
