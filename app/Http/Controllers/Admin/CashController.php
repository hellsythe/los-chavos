<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function report()
    {
        return view('back.cash.report');
    }
}
