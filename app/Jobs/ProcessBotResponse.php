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
        $messageTypes = [];

        foreach ($messages as $message) {
            $text = $this->extractMessageText($message, $media);
            $messageTypes[] = $message->type . ($text ? '' : '(no_text)');
            if ($text !== null && trim($text) !== '') {
                $queryParts[] = $text;
            }
        }

        $query = trim(implode("\n", $queryParts));

        Log::channel('bot')->info('Bot processing started', [
            'chat_id' => $chat->id,
            'messages_count' => $messages->count(),
            'message_types' => $messageTypes,
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

        $history = $this->buildHistory($chat);
        $searchQuery = $this->enrichQueryWithHistory($query, $history);

        try {
            $contextChunks = $qdrant->search($searchQuery, (int) config('openai_llm.chat_bot.context_top_k'));
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

        $topScore = $this->getTopScore($contextChunks);
        $minScore = (float) config('openai_llm.chat_bot.min_relevance_score', 0.3);

        $isOffTopic = $this->isOffTopic($searchQuery, $topScore, $minScore);
        try {
            $messages_payload = $chatService->buildBotPrompt(
                userQuery: $query,
                contextChunks: $contextChunks,
                history: $history,
                businessContext: $businessContext,
            );

            if ($isOffTopic) {
                $replyText = (string) config('openai_llm.chat_bot.off_topic_message');
                Log::channel('bot')->info('Off-topic detected, using off-topic message', [
                    'chat_id' => $chat->id,
                    'top_score' => $topScore,
                    'query' => mb_substr($query, 0, 100),
                ]);
            } elseif (empty($contextChunks)) {
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
            'is_off_topic' => $isOffTopic,
        ]);

        $this->sendReply($chat, $replyText);

        if (! $isOffTopic) {
            $this->sendUniformPhotos($chat, $contextChunks);
        }

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

    /**
     * Send photos of uniforms that were used in the context.
     * Called after the text response when the bot has uniforms in the context.
     */
    protected function sendUniformPhotos(Chat $chat, array $contextChunks, int $maxPhotos = 6): void
    {
        $uniformIds = $this->extractUniformIds($contextChunks);
        if (empty($uniformIds)) {
            return;
        }

        $photos = \App\Models\UniformPhoto::query()
            ->whereIn('uniform_id', $uniformIds)
            ->where('status', \App\Models\UniformPhoto::STATUS_ACTIVE)
            ->orderBy('uniform_id')
            ->orderBy('order')
            ->limit($maxPhotos)
            ->get();

        if ($photos->isEmpty()) {
            return;
        }

        $uniformCache = \App\Models\Uniform::with('school')
            ->whereIn('id', $uniformIds)
            ->get()
            ->keyBy('id');

        foreach ($photos as $photo) {
            $uniform = $uniformCache->get($photo->uniform_id);
            if (! $uniform || empty($photo->photo)) {
                continue;
            }

            $caption = sprintf(
                "%s — %s%s",
                $uniform->name,
                $uniform->school ? $uniform->school->name : '',
                $uniform->description ? "\n" . $uniform->description : ''
            );

            try {
                resolve(SendMessage::class)->Send([
                    'waba_phone_id' => $chat->waba_phone_id,
                    'to' => $chat->client_phone,
                    'message' => [
                        'type' => 'image',
                        'image' => [
                            'link' => $photo->photo,
                            'caption' => mb_substr($caption, 0, 1024),
                        ],
                    ],
                ], 'BOT');
            } catch (\Throwable $e) {
                Log::channel('bot')->warning('Failed to send uniform photo', [
                    'chat_id' => $chat->id,
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('bot')->info('Sent uniform photos', [
            'chat_id' => $chat->id,
            'count' => $photos->count(),
            'uniform_ids' => $uniformIds,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $contextChunks
     * @return array<int, int>
     */
    protected function extractUniformIds(array $contextChunks): array
    {
        $ids = [];
        foreach ($contextChunks as $chunk) {
            if (($chunk['type'] ?? '') !== 'uniform') {
                continue;
            }
            $payloadId = $chunk['payload']['id'] ?? null;
            if ($payloadId !== null) {
                $ids[] = (int) $payloadId;
            }
        }
        return array_values(array_unique($ids));
    }

    protected function markProcessed($messages): void
    {
        $now = now();
        $ids = $messages->pluck('id')->all();
        Message::query()->whereIn('id', $ids)->update(['processed_by_bot_at' => $now]);
    }

    protected function getTopScore(array $contextChunks): ?float
    {
        if (empty($contextChunks)) {
            return null;
        }
        $scores = array_map(fn ($c) => $c['score'] ?? null, $contextChunks);
        $scores = array_filter($scores, fn ($s) => $s !== null);
        if (empty($scores)) {
            return null;
        }
        return max($scores);
    }

    /**
     * Detect off-topic queries using two signals:
     * 1. Heuristic: common Spanish question words about unrelated topics.
     * 2. Semantic: top Qdrant score below threshold (means the query is not about our data).
     */
    protected function isOffTopic(string $query, ?float $topScore, float $minScore): bool
    {
        $normalized = mb_strtolower(trim($query));
        if ($normalized === '') {
            return true;
        }

        $offTopicPatterns = [
            '/\bcapital de\b/iu',
            '/\bpor qu[eé]\b/iu',
            '/\bqu[eé]\s+es\b/iu',
            '/\bqu[eé]\s+son\b/iu',
            '/\bcu[aá]ndo\s+(naci[oó]|se fund[oó]|empez[oó]|termin[oó]|fue descubierto)\b/iu',
            '/\bqui[eé]n\s+(descubri[oó]|invent[oó]|fue|cre[oó])\b/iu',
            '/\bc[oó]mo\s+(se hace|funciona|se prepara|se hace un)\b/iu',
            '/\bdime\s+(un chiste|algo de|qu[eé] sabes de)\b/iu',
            '/\bcu[eé]ntame\s+(un|algo|sobre)\b/iu',
            '/\bqu[eé]\s+(d[ií]a es|hora es|tiempo hace|pasó)\b/iu',
            '/\bqu[eé]\s+(planetas?|idiomas?|continentes?|pa[ií]ses?|animales?)\b/iu',
            '/\bprogramaci[oó]n\b/iu',
            '/\bmatem[aá]ticas?\b/iu',
            '/\bhistoria\s+de\b/iu',
            '/\breceta\s+de\b/iu',
            '/\bel\s+tiempo\b/iu',
            '/\bel\s+clima\b/iu',
        ];

        foreach ($offTopicPatterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        if ($topScore !== null && $topScore < $minScore) {
            return true;
        }

        return false;
    }

    /**
     * If the current query is short/ambiguous (e.g., "y el de educación física?"),
     * enrich it with the most recent user message from history so the search
     * can find the relevant school.
     */
    protected function enrichQueryWithHistory(string $query, array $history): string
    {
        $current = mb_strtolower(trim($query));
        if (mb_strlen($current) < 60 && ! empty($history)) {
            foreach (array_reverse($history) as $msg) {
                if (($msg['role'] ?? '') === 'user') {
                    $userMsg = mb_strtolower(trim($msg['content'] ?? ''));
                    if (mb_strlen($userMsg) > 10) {
                        return $userMsg . ' | ' . $query;
                    }
                }
            }
        }
        return $query;
    }
}
