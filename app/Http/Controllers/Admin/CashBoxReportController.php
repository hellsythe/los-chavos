<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use Illuminate\Http\Request;
use Sdkconsultoria\Core\Controllers\Traits\ApiControllerTrait;

class CashBoxReportController extends ResourceController
{
    protected $model = \App\Models\CashBoxReport::class;
    use ApiControllerTrait;
    protected $view = 'back.cash-box-report';

    public function viewAny(Request $request)
    {
        $model = new $this->model;
        $this->authorize('viewAny', $model);

        $query = $model::where('status', $model::STATUS_CLOSE);
        $query = $this->searchable($query, $request);
        $query = $this->applyOrderByToQuery($query, $request->input('order'));

        return $this->setPagination($query, $request);
    }

}
