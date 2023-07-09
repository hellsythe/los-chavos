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
        $payments = $this->getPaymentsFromToday();

        return view('back.cash.report', [
            'payments' => $payments
        ]);
    }

    protected function getPaymentsFromToday()
    {
        $tomorrow = date('Y-m-d', strtotime("+1 days"));
        $now = date('Y-m-d');
        $lastCashBox = CashBoxReport::where('status', CashBoxReport::STATUS_OPEN)->first();
        $lastCash = 0;
        if ($lastCashBox) {
            $lastCash =  $lastCashBox->real_cash - $lastCashBox->out_cash;
        }

        return [
            'cash' => Payment::whereBetween('created_at', [$now, $tomorrow])->where('payment_method','cash')->sum('amount') + $lastCash,
            'card' => Payment::whereBetween('created_at', [$now, $tomorrow])->where('payment_method','card')->sum('amount'),
        ];
    }
}
