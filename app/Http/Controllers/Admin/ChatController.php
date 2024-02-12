<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\PlanningOrder;

class ChatController extends Controller
{
    public function index()
    {
        return view('back.chat.index', [
        ]);
    }
}
