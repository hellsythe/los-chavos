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
            'urls' => [
                'get_conversations'=> route('conversation.index'),
                'get_messages' => route('message.index'),
                'send_message' => route('message.send'),
            ]
        ]);
    }
}
