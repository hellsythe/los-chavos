<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBoxReport;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{
    public function report()
    {
        $payments = $this->getPaymentsFromLastCashBoxReportToNow();

        return view('back.cash.report', [
            'payments' => $payments
        ]);
    }

    protected function getPaymentsFromLastCashBoxReportToNow()
    {
        $last_report = CashBoxReport::where('status', CashBoxReport::STATUS_OPEN)->first();

        $payments = Payment::select([DB::raw('SUM(amount) as total'), 'payment_method']);

        if ($last_report) {
            $payments = $payments->where('created_at', '>', $last_report->finish);
        }

        return $payments->groupBy('payment_method')->get()->toArray();

    }
}
