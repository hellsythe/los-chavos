<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentController extends ResourceController
{
    protected $view = 'back.payment';

    protected $model = \App\Models\Payment::class;

    protected function customFilters($query, $request)
    {
        $query = $query->orderBy('id', 'DESC');
        return $query->whereBetween('created_at', [$request->start, $request->end]);
    }

    protected function filters(): array
    {
        return [
            'method' => function ($query, $value) {
                return $query->where('payment_method', $value);
            },
        ];
    }

    public function report(Request $request)
    {
        $payments = $this->model::whereBetween('created_at', [$request->start, $request->end]);

        if ($request->method) {
            $payments = $payments->where('payment_method', $request->method);
        }
        $pdf = Pdf::loadView('back.payment.report', [
            'payments' => $payments->get(),
            'start' => Carbon::parse($request->start)->format('l j F Y H:i'),
            'end' => Carbon::parse($request->end)->format('l j F Y H:i'),
            'method' => $this->getMethod($request->method),
            'total' => $payments->sum('amount'),
        ]);

        return $pdf->download();
    }

    protected function getMethod($method)
    {
        switch ($method) {
            case 'cash':
                return 'Efectivo';
                break;

            case 'card':
                return 'Tarjeta';
                break;

            default:
                return 'Cualquiera';
                break;
        }
    }
}
