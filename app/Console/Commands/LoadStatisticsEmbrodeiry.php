<?php

namespace App\Console\Commands;

use App\Models\EmbroideryStatistics;
use App\Models\Order;
use DateTime;
use Illuminate\Console\Command;

class LoadStatisticsEmbrodeiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-statistics-embrodeiry {date?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga las estadisticas de bordado del dia indicado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->getStatisticsFromAllOrders();
        } else {
            $date = $this->argument('date') ?? date('Y-m-d');
            $this->getStatisticsFromDate($date);
        }

        $this->info('Estadisticas de bordado cargadas correctamente');
    }

    private function getStatisticsFromAllOrders()
    {
        $begin = new DateTime('2023-04-25');
        $end   = new DateTime();

        for($i = $begin; $i <= $end; $i->modify('+1 day')){
            $this->getStatisticsFromDate($i->format("Y-m-d"));
        }
    }

    private function getStatisticsFromDate(string $date)
    {
        $embroderies = 0;
        $minutes = 0;
        $services = 0;

        $orders = Order::where('finished_at', $date)->get();

        foreach ($orders as $order) {
            $embroderies += $order->garment_total;
            $minutes += $order->minutes_total;
            $services += count($order->details);
        }

        $this->setStatisticsModelFromDate($date, $embroderies, $minutes, $services, count($orders));
    }

    private function setStatisticsModelFromDate(string $date, $embroderies, $minutes, $services, $orders)
    {
        if($embroderies == 0){
            return;
        }

        $statistics = EmbroideryStatistics::where('date', '=', $date)->first();

        if (!$statistics) {
            $statistics = new EmbroideryStatistics();
            $statistics->date = $date;
        }

        $statistics->embroderies = $embroderies;
        $statistics->minutes = $minutes;
        $statistics->services = $services;
        $statistics->orders = $orders;
        $statistics->status = EmbroideryStatistics::STATUS_ACTIVE;
        $statistics->save();

        return $statistics;
    }
}
