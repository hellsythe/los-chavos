<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Http\Request;
use Sdkconsultoria\Core\Controllers\ResourceController;

class SchoolController extends ResourceController
{
    protected $model = \App\Models\School::class;

    public function show(Request $request, $id)
    {
        $model = $this->model::findModel($id);
        $model->isAuthorize('view');

        return view('back.school.show', [
            'model' => $model,
        ]);
    }
}
