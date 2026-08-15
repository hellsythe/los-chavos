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
            ->selectRaw('MAX(CAST(timestamp AS UNSIGNED))')
            ->whereColumn('chat_id', 'chats.id');

        $query = $models
            ->orderByRaw('COALESCE((' . $lastMessageSubquery->toSql() . '), UNIX_TIMESTAMP(chats.last_message), UNIX_TIMESTAMP(chats.updated_at), UNIX_TIMESTAMP(chats.created_at)) DESC')
            ->orderBy('chats.id', 'DESC');

        if ($request->boolean('unread')) {
            $query->where('chats.unread_messages', '>', 0);
        }

        return $query;
    }
}
