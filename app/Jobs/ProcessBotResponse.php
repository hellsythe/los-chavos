<?php

namespace App\Jobs;

use App\Services\AI\MediaProcessor;
use App\Services\AI\OpenAiChatService;
use App\Services\AI\QdrantService;
use App\Services\BusinessInfoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sdkconsultoria\WhatsappCloudApi\Lib\Message\SendMessage;
use Sdkconsultoria\WhatsappCloudApi\Models\Chat;
use Sdkconsultoria\WhatsappCloudApi\Models\Message;

class ProcessBotResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(public int $chatId)
    {
    }

    public function handle(
        QdrantService $qdrant,
        OpenAiChatService $chatService,
        MediaProcessor $media,
        BusinessInfoService $businessInfo,
    ): void {
        $chat = Chat::find($this->chatId);
        if (! $chat || ! $chat->bot) {
            Log::channel('bot')->info('Skipped bot job', ['chat_id' => $this->chatId, 'reason' => 'chat not found or bot disabled']);
            return;
        }

        $debounceTolerance = (int) config('openai_llm.chat_bot.debounce_seconds') - 1;
        if ($debounceTolerance < 1) {
            $debounceTolerance = 1;
        }

        $lastInbound = $chat->getAttribute('bot_last_inbound_at');
        if ($lastInbound) {
            $lastInboundAt = $lastInbound instanceof \Carbon\Carbon ? $lastInbound : \Carbon\Carbon::parse($lastInbound);
            if ($lastInboundAt->diffInSeconds(now()) < $debounceTolerance) {
                Log::channel('bot')->info('Bot job re-delayed (still in debounce window)', [
                    'chat_id' => $chat->id,
                    'last_inbound_seconds_ago' => $lastInboundAt->diffInSeconds(now()),
                ]);
                self::dispatch($this->chatId)->delay(now()->addSeconds(2));
                return;
            }
        }

        $chat->bot_pending_response_at = null;
        $chat->save();

        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->where('direction', 'toApp')
            ->where('status', '!=', 'reaction')
            ->whereNull('processed_by_bot_at')
            ->orderBy('timestamp')
            ->orderBy('id')
            ->get();

        if ($messages->isEmpty()) {
            Log::channel('bot')->info('No unprocessed messages', ['chat_id' => $chat->id]);
            return;
        }

        $queryParts = [];

        foreach ($messages as $message) {
            $text = $this->extractMessageText($message, $media);
            if ($text !== null && trim($text) !== '') {
                $queryParts[] = $text;
            }
        }

        $query = trim(implode("\n", $queryParts));

        Log::channel('bot')->info('Bot processing started', [
            'chat_id' => $chat->id,
            'messages_count' => $messages->count(),
            'query_preview' => mb_substr($query, 0, 200),
        ]);

        if ($query === '') {
            $this->markProcessed($messages);
            Log::channel('bot')->info('Empty query, marking processed', ['chat_id' => $chat->id]);
            return;
        }

        $contextChunks = [];
        $replyText = null;
        $error = null;

        try {
            $contextChunks = $qdrant->search($query, (int) config('openai_llm.chat_bot.context_top_k'));
            Log::channel('bot')->info('Qdrant search', [
                'chat_id' => $chat->id,
                'chunks_found' => count($contextChunks),
                'top_scores' => array_map(fn ($c) => $c['score'] ?? null, array_slice($contextChunks, 0, 3)),
            ]);
        } catch (\Throwable $e) {
            Log::channel('bot')->error('Qdrant search failed', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
        }

        $history = $this->buildHistory($chat);

        $businessContext = $businessInfo->getBusinessContext();

        try {
            $messages_payload = $chatService->buildBotPrompt(
                userQuery: $query,
                contextChunks: $contextChunks,
                history: $history,
                businessContext: $businessContext,
            );

            if (empty($contextChunks)) {
                $replyText = (string) config('openai_llm.chat_bot.no_context_message');
                Log::channel('bot')->info('No context, using fallback message', ['chat_id' => $chat->id]);
            } else {
                $replyText = $chatService->chat($messages_payload);
            }
        } catch (\Throwable $e) {
            Log::channel('bot')->error('OpenAI chat failed', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
            $error = $e;
        }

        if ($error !== null) {
            $replyText = (string) config('openai_llm.chat_bot.fallback_message');
        }

        Log::channel('bot')->info('Bot reply generated', [
            'chat_id' => $chat->id,
            'reply_preview' => mb_substr($replyText, 0, 200),
            'reply_length' => mb_strlen($replyText),
        ]);

        $this->sendReply($chat, $replyText);

        $this->markProcessed($messages);
    }

    protected function extractMessageText(Message $message, MediaProcessor $media): ?string
    {
        $type = $message->type;
        $body = $message->body ? json_decode($message->body, true) : null;

        if ($type === 'text') {
            return $body['text']['body'] ?? null;
        }

        if (in_array($type, ['audio', 'image'], true)) {
            $url = $body[$type]['url'] ?? null;
            return $url ? $media->extractText($type, $url, $body) : null;
        }

        if ($type === 'document') {
            return null;
        }

        return null;
    }

    protected function buildHistory(Chat $chat): array
    {
        $limit = (int) config('openai_llm.chat_bot.max_history', 5);
        if ($limit <= 0) {
            return [];
        }

        $recent = Message::query()
            ->where('chat_id', $chat->id)
            ->where('status', '!=', 'reaction')
            ->orderBy('timestamp', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $history = [];
        foreach ($recent as $msg) {
            $role = $msg->direction === 'toApp' ? 'user' : 'assistant';
            $text = $this->extractMessageText($msg, app(MediaProcessor::class));
            if ($text === null || trim($text) === '') {
                continue;
            }
            $history[] = ['role' => $role, 'content' => $text];
        }

        return $history;
    }

    protected function sendReply(Chat $chat, string $text): void
    {
        if (! $chat->waba_phone_id) {
            Log::warning('Chat without waba_phone_id, cannot send bot reply', [
                'chat_id' => $chat->id,
            ]);
            return;
        }

        try {
            resolve(SendMessage::class)->Send([
                'waba_phone_id' => $chat->waba_phone_id,
                'to' => $chat->client_phone,
                'message' => [
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $text,
                    ],
                ],
            ], 'BOT');
        } catch (\Throwable $e) {
            Log::error('Failed to send bot reply', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function markProcessed($messages): void
    {
        $now = now();
        $ids = $messages->pluck('id')->all();
        Message::query()->whereIn('id', $ids)->update(['processed_by_bot_at' => $now]);
    }
}
