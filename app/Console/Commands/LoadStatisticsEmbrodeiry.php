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

        $orders = Order::where('finished_at', $date)->get();

        foreach ($orders as $order) {
            $statistics = $this->getStatisticsFromOrder($order);

            if ($statistics) {
                $embroderies += $statistics['embroderies'];
                $minutes += $statistics['minutes'];
            }
        }

        $this->setStatisticsModelFromDate($date, $embroderies, $minutes);
    }

    private function getStatisticsFromOrder(Order $order)
    {
        foreach ($order->details as $detail) {
            if ($detail->service_id == 1) {
                $minutes = 0;

                if($detail->detail->design){
                    $minutes = $detail->detail->design->minutes;
                }

                return [
                    'embroderies' => $detail->garment_amount,
                    'minutes' => $minutes,
                ];
            }

            return false;
        }
    }

    private function setStatisticsModelFromDate(string $date, $embroderies, $minutes)
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
        $statistics->status = EmbroideryStatistics::STATUS_ACTIVE;
        $statistics->save();

        return $statistics;
    }
}
