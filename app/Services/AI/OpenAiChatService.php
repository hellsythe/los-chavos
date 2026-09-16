<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiChatService
{
    public function __construct(
        protected string $apiKey,
        protected string $model,
        protected string $apiBase,
        protected int $timeout,
    ) {
    }

    public static function make(): self
    {
        return new self(
            apiKey: (string) config('openai_llm.api_key'),
            model: (string) config('openai_llm.chat_model'),
            apiBase: (string) config('openai_llm.api_base'),
            timeout: (int) config('openai_llm.timeout'),
        );
    }

    /**
     * Send a chat completion request.
     *
     * @param  array<int, array{role:string, content:string|array<int, array<string, mixed>>}>  $messages
     * @param  bool  $jsonMode  Force the model to return a JSON object (response_format=json_object).
     */
    public function chat(array $messages, ?float $temperature = 0.3, bool $jsonMode = false): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post(rtrim($this->apiBase, '/').'/chat/completions', $payload);

        if ($response->failed()) {
            Log::error('OpenAI chat failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('OpenAI chat failed: '.$response->status());
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new \RuntimeException('OpenAI chat returned no content');
        }

        return trim($content);
    }

    /**
     * Build a standardized prompt for the customer-service bot.
     *
     * @param  array<int, array{role:string, content:string}>  $history
     * @param  array<int, array<string, mixed>>  $contextChunks
     */
    public function buildBotPrompt(
        string $userQuery,
        array $contextChunks,
        array $history = [],
        ?string $businessContext = null,
        ?string $systemPrompt = null,
    ): array {
        $systemPrompt ??= (string) config('openai_llm.chat_bot.system_prompt');

        $contextText = $this->formatContext($contextChunks);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        if ($businessContext !== null && trim($businessContext) !== '') {
            $messages[] = [
                'role' => 'system',
                'content' => "INFORMACIÓN DEL NEGOCIO (fecha actual, horarios, feriados):\n".$businessContext,
            ];
        }

        if ($contextText !== '') {
            $messages[] = [
                'role' => 'system',
                'content' => "CONTEXTO RECUPERADO (úsalo para responder, no inventes datos):\n".$contextText,
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => 'No se encontró contexto en la base de conocimiento. Responde indicando que necesitas más información (escuela, nivel educativo, tipo de uniforme).',
            ];
        }

        $messages[] = [
            'role' => 'system',
            'content' => "FORMATO DE RESPUESTA (OBLIGATORIO): Responde ÚNICAMENTE con un objeto JSON válido con esta estructura exacta:\n"
            . "{\n"
            . "  \"reply\": \"<tu respuesta al cliente en español, breve y cordial>\",\n"
            . "  \"send_photos\": <true o false>\n"
            . "}\n"
            . "Reglas para 'send_photos':\n"
            . "- true SOLO si el cliente pidió explícitamente ver fotos, imágenes, modelos, catálogo o ejemplos visuales de uniformes.\n"
            . "- true también si el cliente dice frases como: 'muéstrame', 'mándame', 'envíame', 'quiero ver', 'ver modelos', 'cómo se ve', 'cómo queda', 'tienes fotos', 'hay imágenes', 'enséñame', 'me puedes mostrar'.\n"
            . "- false si el cliente solo pregunta por precio, horarios, ubicación, disponibilidad textual, tallas, contacto, o cualquier cosa donde las fotos NO le ayudarían.\n"
            . "- false por defecto cuando tengas duda. Es preferible NO enviar fotos a enviar fotos no deseadas."
        ];

        foreach ($history as $entry) {
            $messages[] = [
                'role' => $entry['role'],
                'content' => $entry['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userQuery,
        ];

        return $messages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $chunks
     */
    protected function formatContext(array $chunks): string
    {
        if (empty($chunks)) {
            return '';
        }

        $lines = [];
        foreach ($chunks as $i => $chunk) {
            $type = $chunk['type'] ?? 'item';
            $label = $chunk['label'] ?? $type.' #'.($chunk['id'] ?? ($i + 1));
            $text = $chunk['text'] ?? '';
            $lines[] = sprintf("[%d] (%s) %s\n%s", $i + 1, $type, $label, $text);
        }

        return implode("\n\n", $lines);
    }
}
