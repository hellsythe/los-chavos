<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\NewOrder;

class DashboardController extends Controller
{
    public function index()
    {
        // NewOrder::dispatch('anime');

        return view('back.dashboard.index', [
        ]);
    }
}
