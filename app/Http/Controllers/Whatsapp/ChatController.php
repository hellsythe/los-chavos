<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Sdkconsultoria\WhatsappCloudApi\Http\Controllers\ChatController as SdkChatController;

class ChatController extends SdkChatController
{
    protected function defaultOptions($models, Request $request)
    {
        return $models
            ->orderByRaw('COALESCE(last_message, updated_at) DESC')
            ->orderBy('id', 'DESC');
    }
}
