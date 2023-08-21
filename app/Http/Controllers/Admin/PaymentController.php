<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use Illuminate\Http\Request;

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

    // public function viewAny(Request $request)
    // {
    //     $model = new $this->model;
    //     $this->authorize('viewAny', $model);

    //     $query = $model::where('orders.status', '>', $model::STATUS_ACTIVE);
    //     $query = $model::where('orders.status', '<', $model::STATUS_FINISH);
    //     $query = $this->searchable($query, $request);
    //     $query = $this->customFilters($query, $request);
    //     $query = $this->applyOrderByToQuery($query, $request->input('order'));

    //     return $this->setPagination($query, $request);
    // }
}
