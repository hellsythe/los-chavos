<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class ClientController extends ResourceController
{
    protected $model = \App\Models\Client::class;
}
