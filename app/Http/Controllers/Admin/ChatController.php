<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use App\Models\PlanningOrder;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;

class ChatController extends Controller
{
    public function index()
    {
        return view('back.chat.index', [
            'urls' => [
                'get_conversations' => route('chat.index'),
                'get_messages' => route('message.index'),
                'send_message' => route('message.send'),
            ]
        ]);
    }

    public function unread()
    {
        return response()->json(Chat::where('unread_messages', '>', 0)->sum('unread_messages'));
    }
}
