<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    public function __construct(
        protected string $apiKey,
        protected string $model,
        protected string $apiBase,
        protected int $timeout,
        protected int $vectorSize,
    ) {
    }

    public static function make(): self
    {
        return new self(
            apiKey: (string) config('openai_llm.api_key'),
            model: (string) config('openai_llm.embedding_model'),
            apiBase: (string) config('openai_llm.api_base'),
            timeout: (int) config('openai_llm.timeout'),
            vectorSize: (int) config('openai_llm.vector_size'),
        );
    }

    /**
     * Generate an embedding vector for the given text.
     * Uses a 24h cache to avoid re-embedding identical content.
     *
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return array_fill(0, $this->vectorSize, 0.0);
        }

        if (empty($this->apiKey)) {
            Log::channel('bot')->error('OPEN_AI_API_KEY is empty - check .env file');
            throw new \RuntimeException('OPEN_AI_API_KEY is not configured');
        }

        if (! str_starts_with($this->apiKey, 'sk-')) {
            Log::channel('bot')->warning('OPEN_AI_API_KEY does not start with sk- - may be invalid');
        }

        $cacheKey = 'embed:'.sha1($this->model.':'.$text);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($text) {
            return $this->callOpenAi($text);
        });
    }

    /**
     * @return array<int, float>
     */
    protected function callOpenAi(string $text): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post(rtrim($this->apiBase, '/').'/embeddings', [
                'model' => $this->model,
                'input' => $text,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI embeddings failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('OpenAI embeddings failed: '.$response->status());
        }

        $embedding = $response->json('data.0.embedding');

        if (! is_array($embedding)) {
            throw new \RuntimeException('OpenAI embeddings returned no data');
        }

        return array_map('floatval', $embedding);
    }
}
