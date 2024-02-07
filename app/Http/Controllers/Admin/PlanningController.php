<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class PlanningController extends ResourceController
{
    protected $model = \App\Models\Planning::class;
    protected $view = 'back.planning';
}
