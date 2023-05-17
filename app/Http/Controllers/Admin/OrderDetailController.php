<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Sdkconsultoria\Core\Controllers\ResourceController;
use Sdkconsultoria\Core\Controllers\Traits\ApiControllerTrait;
use Illuminate\Http\Request;

class OrderDetailController extends ResourceController
{
    protected $model = \App\Models\OrderDetail::class;
    use ApiControllerTrait;

    public function viewAny(Request $request)
    {
        $model = new $this->model;
        $this->authorize('viewAny', $model);

        $query = $model::select(['order_details.*', 'orders.deadline'])->where('orders.status', '=', Order::STATUS_PENDING);
        $query = $this->searchable($query, $request)->leftJoin('orders', 'orders.id', '=', 'order_id');
        $query = $this->applyOrderByToQuery($query, $request->input('order'));
        $query = $query->with('service', 'subservice', 'order');
        $query = $query->orderBy('orders.deadline', 'ASC');

        return $this->setPagination($query, $request);
        // return $data->each(fn ($data) => $data->order->append('deadline'));
    }

    public function show(Request $request, $id)
    {
        $model = $this->model::findModel($id);
        return redirect('/admin/order/'.$model->order_id);
    }

    protected function filters(): array
    {
        return [
            'deadline' => function ($query, $value) {
                return $query->where('orders.deadline', 'like', '%' . $value . '%');
            },
        ];
    }
}
