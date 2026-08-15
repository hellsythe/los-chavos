<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaProcessor
{
    public function __construct(
        protected string $apiKey,
        protected string $apiBase,
        protected int $timeout,
        protected string $chatModel,
    ) {
    }

    public static function make(): self
    {
        return new self(
            apiKey: (string) config('openai_llm.api_key'),
            apiBase: (string) config('openai_llm.api_base'),
            timeout: (int) config('openai_llm.timeout'),
            chatModel: (string) config('openai_llm.chat_model'),
        );
    }

    /**
     * Convert a media message into a processable text string.
     * Returns null if the media type is unsupported.
     */
    public function extractText(string $type, ?string $mediaUrl, array $rawContent): ?string
    {
        if (! $mediaUrl) {
            Log::channel('bot')->warning('MediaProcessor: empty media url', [
                'type' => $type,
                'rawContent' => $rawContent,
            ]);
            return null;
        }

        return match ($type) {
            'audio' => $this->transcribeAudio($mediaUrl),
            'image' => $this->describeImage($mediaUrl),
            'document' => null,
            default => null,
        };
    }

    protected function transcribeAudio(string $publicUrl): ?string
    {
        try {
            $localPath = $this->downloadToTemp($publicUrl);
            if (! $localPath) {
                Log::channel('bot')->warning('Audio transcription: downloadToTemp returned null', [
                    'url' => $publicUrl,
                ]);
                return null;
            }

            $fileSize = filesize($localPath);
            Log::channel('bot')->info('Audio file downloaded for transcription', [
                'url' => $publicUrl,
                'local_path' => $localPath,
                'file_size' => $fileSize,
            ]);

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson()
                ->attach(
                    'file',
                    file_get_contents($localPath),
                    basename($localPath),
                )
                ->post(rtrim($this->apiBase, '/').'/audio/transcriptions', [
                    'model' => 'whisper-1',
                    'response_format' => 'text',
                    'language' => 'es',
                ]);

            @unlink($localPath);

            if ($response->failed()) {
                Log::channel('bot')->error('Whisper transcription failed', [
                    'url' => $publicUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $body = trim((string) $response->body());

            if ($body === '') {
                Log::channel('bot')->warning('Whisper returned empty text', [
                    'url' => $publicUrl,
                    'file_size' => $fileSize,
                ]);
                return null;
            }

            return '[Audio transcrito]: '.$body;
        } catch (\Throwable $e) {
            Log::channel('bot')->error('Audio transcription error', [
                'url' => $publicUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    protected function describeImage(string $publicUrl): ?string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson()
                ->post(rtrim($this->apiBase, '/').'/chat/completions', [
                    'model' => $this->chatModel,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un asistente que describe imágenes de manera breve y objetiva en español. Si ves texto/datos específicos (precios, tallas, modelos), repórtalos. Si no puedes describir la imagen, dilo.',
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => 'Describe esta imagen en máximo 2 oraciones.'],
                                ['type' => 'image_url', 'image_url' => ['url' => $publicUrl]],
                            ],
                        ],
                    ],
                    'max_tokens' => 200,
                ]);

            if ($response->failed()) {
                Log::error('Image description failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');

            return is_string($content) && trim($content) !== ''
                ? '[Imagen adjunta]: '.trim($content)
                : null;
        } catch (\Throwable $e) {
            Log::error('Image description error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Download a public file URL to a local temp file.
     */
    protected function downloadToTemp(string $publicUrl): ?string
    {
        try {
            $relative = $this->publicUrlToRelativePath($publicUrl);
            if ($relative && Storage::disk('public')->exists($relative)) {
                $absolute = Storage::disk('public')->path($relative);
                $tmp = tempnam(sys_get_temp_dir(), 'mc_');
                if (! copy($absolute, $tmp)) {
                    Log::channel('bot')->error('Failed to copy local file to temp', [
                        'absolute' => $absolute,
                        'tmp' => $tmp,
                    ]);
                    return null;
                }
                return $tmp;
            }

            $tmp = tempnam(sys_get_temp_dir(), 'mc_');
            $response = Http::timeout($this->timeout)->get($publicUrl);
            if ($response->failed()) {
                Log::channel('bot')->error('HTTP download failed', [
                    'url' => $publicUrl,
                    'status' => $response->status(),
                ]);
                return null;
            }
            $content = $response->body();
            if (! $content) {
                Log::channel('bot')->warning('HTTP download returned empty body', [
                    'url' => $publicUrl,
                ]);
                return null;
            }
            file_put_contents($tmp, $content);
            return $tmp;
        } catch (\Throwable $e) {
            Log::channel('bot')->error('Media download failed', [
                'url' => $publicUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    protected function publicUrlToRelativePath(string $publicUrl): ?string
    {
        if (str_contains($publicUrl, '/storage/')) {
            return substr($publicUrl, strpos($publicUrl, '/storage/') + strlen('/storage/'));
        }
        return null;
    }
}
