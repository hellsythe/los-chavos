<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class DesignPrintController extends ResourceController
{
    protected $view = 'back.design-print';

    protected $model = \App\Models\DesignPrint::class;
}
