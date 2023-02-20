<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class PaymentController extends ResourceController
{
    protected $model = \App\Models\Payment::class;
}
