<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class DesignController extends ResourceController
{
    protected $model = \App\Models\Design::class;
    protected $view = 'back.design';

}
