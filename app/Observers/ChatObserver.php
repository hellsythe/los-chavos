<?php

namespace App\Observers;

use Sdkconsultoria\WhatsappCloudApi\Models\Chat;

class ChatObserver
{
    public function creating(Chat $chat): void
    {
        $chat->setAttribute('bot', true);
    }
}
