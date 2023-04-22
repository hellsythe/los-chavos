<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Sdkconsultoria\Core\Controllers\ResourceController;

class DesignController extends ResourceController
{
    protected $model = \App\Models\Design::class;
    protected $view = 'back.design';

    public function view(Request $request, $id)
    {
        $model = $this->model::withTrashed()->where('id', $id)->first();
        $this->authorize('view', $model);

        return response()
            ->json(['model' => $model->getAttributes()]);
    }
}
