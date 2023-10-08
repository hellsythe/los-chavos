<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;
use Sdkconsultoria\Core\Controllers\Traits\ApiControllerTrait;

class SettingController extends ResourceController
{
    use ApiControllerTrait;
    protected $model = \App\Models\Setting::class;
    protected $view = 'back.setting';
}
