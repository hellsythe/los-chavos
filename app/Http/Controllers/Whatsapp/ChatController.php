<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sdkconsultoria\WhatsappCloudApi\Http\Controllers\ChatController as SdkChatController;

class ChatController extends SdkChatController
{
    protected function defaultOptions($models, Request $request)
    {
        $lastMessageSubquery = DB::table('messages')
            ->selectRaw('MAX(timestamp)')
            ->whereColumn('chat_id', 'chats.id');

        return $models
            ->orderByRaw('COALESCE((' . $lastMessageSubquery->toSql() . '), chats.updated_at, chats.created_at) DESC')
            ->orderBy('chats.id', 'DESC');
    }
}
