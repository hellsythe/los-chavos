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
        return view('back.cash.report', [
            'payments' =>  $this->getPaymentsFromToday(),
            'model' => CashBoxReport::where('status',CashBoxReport::STATUS_CLOSE)->where('start', date('Y-m-d'))->first()
        ]);
    }

    protected function getPaymentsFromToday()
    {
        $tomorrow = date('Y-m-d', strtotime("+1 days"));
        $now = date('Y-m-d');

        return [
            'cash' => Payment::whereBetween('created_at', [$now, $tomorrow])->where('payment_method','cash')->sum('amount'),
            'card' => Payment::whereBetween('created_at', [$now, $tomorrow])->where('payment_method','card')->sum('amount'),
        ];
    }

    public function save(Request $request)
    {
        $cashbox = new CashBoxReport();
        $cashbox->status = CashBoxReport::STATUS_CLOSE;
        $cashbox->real_cash = $request->cash;
        $cashbox->real_card = $request->card;
        $cashbox->calculate_card = $request->cardCalc;
        $cashbox->calculate_cash = $request->cashCalc;
        $cashbox->missing_card = $request->cardCalc - $request->card;
        $cashbox->missing_cash =  $request->cashCalc - $request->cash;
        $cashbox->start = date('Y-m-d');
        $cashbox->finish = date('Y-m-d');
        $cashbox->created_by = auth()->user()->id;
        $cashbox->save();

        return ['status' => 'ok'];
    }
}
