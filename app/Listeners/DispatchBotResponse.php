<?php

namespace App\Listeners;

use App\Jobs\ProcessBotResponse;
use Illuminate\Support\Facades\Log;
use Sdkconsultoria\WhatsappCloudApi\Events\NewWhatsappMessageHook;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;

class DispatchBotResponse
{
    public function handle(NewWhatsappMessageHook $event): void
    {
        $chatId = is_array($event->chat) ? ($event->chat['chat_id'] ?? null) : ($event->chat->id ?? null);

        if (! $chatId) {
            return;
        }

        $chat = Chat::find($chatId);
        if (! $chat || ! $chat->bot) {
            return;
        }

        $debounce = (int) config('openai_llm.chat_bot.debounce_seconds', 5);

        $pending = $chat->getAttribute('bot_pending_response_at');
        if ($pending) {
            $pendingAt = $pending instanceof \Carbon\Carbon ? $pending : \Carbon\Carbon::parse($pending);
            if ($pendingAt->isFuture()) {
                return;
            }
        }

        $chat->setAttribute('bot_last_inbound_at', now());
        $chat->setAttribute('bot_pending_response_at', now()->addSeconds($debounce));
        $chat->save();

        try {
            ProcessBotResponse::dispatch($chat->id)->delay(now()->addSeconds($debounce));
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessBotResponse', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
