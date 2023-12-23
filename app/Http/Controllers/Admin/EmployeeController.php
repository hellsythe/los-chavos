<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class EmployeeController extends ResourceController
{
    protected $model = \App\Models\Employee::class;
}
