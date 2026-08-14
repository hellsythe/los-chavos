<?php

namespace App\Services\WhatsApp;

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
        $phoneNumberId = $messageEvent['metadata']['phone_number_id'];
        $wabaPhoneNumber = WabaPhone::where('phone_id', $phoneNumberId)->first();

        $content = $messageEvent['messages'][0];
        $chat = Chat::findOrCreateChat($content['from'], $wabaPhoneNumber);

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

        NewWhatsappMessageHook::dispatch(['chat_id' => $chat->id]);
    }
}
