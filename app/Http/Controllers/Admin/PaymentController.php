<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Date;

class PaymentController extends ResourceController
{
    protected $view = 'back.payment';

    protected $model = \App\Models\Payment::class;

    protected function customFilters($query, $request)
    {
        $query = $query->where('branch_id', session('branch'));
        $request->start = $request->start ?? Date::now()->format('Y-m-d 00:00:00');
        $request->end = $request->end ?? Date::now()->format('Y-m-d 23:59');
        $query = $query->orderBy('id', 'DESC');
        return $query->whereBetween('created_at', [$request->start, $request->end]);
    }

    protected function filters(): array
    {
        return [
            'method' => function ($query, $value) {
                return $query->where('payment_method', $value);
            },
            'service_type' => function ($query, $value) {
                return $query->where($value, '>' , 0);
            },
        ];
    }

    public function report(Request $request)
    {
        $request->start = $request->start ?? Date::now()->format('Y-m-d 00:00:00');
        $request->end = $request->end ?? Date::now()->format('Y-m-d 23:59');

        $payments = $this->model::whereBetween('created_at', [$request->start, $request->end]);

        if ($request->method) {
            $payments = $payments->where('payment_method', $request->method);
        }

        $type = 'Cualquiera';
        if ($request->service_type) {
            $payments = $payments->where($request->service_type, '>' , 0);
            $type = $request->service_type == 'total_embroidery' ? 'Bordado' : 'Estampado';
        }

        $pdf = Pdf::loadView('back.payment.report', [
            'payments' => $payments->get(),
            'start' => Date::parse($request->start)->format('l j F Y H:i'),
            'end' => Date::parse($request->end)->format('l j F Y H:i'),
            'method' => $this->getMethod($request->method),
            'total' => $payments->sum('amount'),
            'total_embroidery' => $payments->sum('total_embroidery'),
            'total_print' => $payments->sum('total_print'),
            'type' => $type,
            'branch_id' => session('branch'),
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
