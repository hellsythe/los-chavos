<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;
use Sdkconsultoria\WhatsappCloudApi\Events\NewWhatsappMessageHook;
use Sdkconsultoria\WhatsappCloudApi\Lib\Message\ReceivedMessage as BaseReceivedMessage;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;
use Sdkconsultoria\WhatsappCloudApi\Models\Message;
use Sdkconsultoria\WhatsappCloudApi\Models\WabaPhone;
use Sdkconsultoria\WhatsappCloudApi\Services\MediaManagerService;

class ReceivedMessage extends BaseReceivedMessage
{
    public function process(array $messageEvent)
    {
        $phoneNumberId = $messageEvent['metadata']['phone_number_id'] ?? null;
        $wabaPhoneNumber = $phoneNumberId ? WabaPhone::where('phone_id', $phoneNumberId)->first() : null;

        if (! isset($messageEvent['messages'][0])) {
            Log::channel('webhook')->warning('Webhook without messages', ['event' => $messageEvent]);
            return;
        }

        $content = $messageEvent['messages'][0];
        $chat = $wabaPhoneNumber
            ? Chat::findOrCreateChat($content['from'], $wabaPhoneNumber)
            : null;

        Log::channel('webhook')->info('Incoming webhook', [
            'type' => $content['type'],
            'from' => $content['from'] ?? null,
            'message_id' => $content['id'] ?? null,
            'chat_id' => $chat?->id,
            'has_context' => isset($content['context']['id']),
        ]);

        switch ($content['type']) {
            case 'unsupported':
            case 'text':
                $this->processIfIsResponse($content);
                $this->processTextMessage($chat, $content);
                break;
            case 'contacts':
                $this->processIfIsResponse($content);
                $this->processTextMessage($chat, $content);
                break;
            case 'document':
            case 'sticker':
            case 'video':
            case 'audio':
            case 'image':
                $this->processIfIsResponse($content);
                $content[$content['type']]['url'] = $this->saveFile($content[$content['type']], $phoneNumberId, $chat);
                $this->processTextMessage($chat, $content);
                break;
            case 'reaction':
                Message::where('message_id', $content['reaction']['message_id'])
                    ->update(['reaction' => $content['reaction']['emoji']]);
                break;
        }

        if ($chat) {
            NewWhatsappMessageHook::dispatch(['chat_id' => $chat->id]);
        }
    }
}
