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
            $fileHeader = $this->readFileHeader($localPath, 16);
            $detectedFormat = $this->detectAudioFormat($fileHeader);

            Log::channel('bot')->info('Audio file downloaded for transcription', [
                'url' => $publicUrl,
                'local_path' => $localPath,
                'file_size' => $fileSize,
                'detected_format' => $detectedFormat,
                'first_bytes_hex' => strtoupper(bin2hex($fileHeader)),
            ]);

            if ($fileSize < 100) {
                Log::channel('bot')->warning('Audio file too small, likely empty', [
                    'url' => $publicUrl,
                    'file_size' => $fileSize,
                ]);
                @unlink($localPath);
                return null;
            }

            if ($detectedFormat === null) {
                Log::channel('bot')->warning('Audio file format not recognized', [
                    'url' => $publicUrl,
                    'first_bytes_hex' => strtoupper(bin2hex($fileHeader)),
                ]);
                @unlink($localPath);
                return null;
            }

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
            ]);
            return null;
        }
    }

    /**
     * Read the first N bytes of a file, returning null if the file can't be read.
     */
    protected function readFileHeader(string $path, int $bytes = 16): string
    {
        $handle = @fopen($path, 'rb');
        if (! $handle) {
            return '';
        }
        $data = @fread($handle, $bytes);
        @fclose($handle);
        return $data ?: '';
    }

    /**
     * Detect the audio format from the file's magic bytes.
     * Returns null if the format is unrecognized.
     */
    protected function detectAudioFormat(string $header): ?string
    {
        if (strlen($header) < 4) {
            return null;
        }

        // OGG: starts with "OggS"
        if (substr($header, 0, 4) === 'OggS') {
            return 'ogg';
        }

        // RIFF/WAV: starts with "RIFF" + "WAVE"
        if (substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WAVE') {
            return 'wav';
        }

        // FLAC: starts with "fLaC"
        if (substr($header, 0, 4) === 'fLaC') {
            return 'flac';
        }

        // MP3: "ID3" tag or 0xFF 0xFB/0xFA/0xF3/0xF2 sync
        if (substr($header, 0, 3) === 'ID3') {
            return 'mp3';
        }
        if (ord($header[0]) === 0xFF && in_array(ord($header[1]) & 0xE0, [0xE0], true)) {
            return 'mp3';
        }

        // MP4/M4A: ... at offset 4 is "ftyp"
        if (substr($header, 4, 4) === 'ftyp') {
            return 'mp4';
        }

        // WebM: starts with EBML header
        if (ord($header[0]) === 0x1A && ord($header[1]) === 0x45 && ord($header[2]) === 0xDF && ord($header[3]) === 0xA3) {
            return 'webm';
        }

        return null;
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
     * Download a public file URL to a local temp file (preserving extension).
     */
    protected function downloadToTemp(string $publicUrl): ?string
    {
        try {
            $extension = $this->extractExtension($publicUrl);
            $relative = $this->publicUrlToRelativePath($publicUrl);
            if ($relative && Storage::disk('public')->exists($relative)) {
                $absolute = Storage::disk('public')->path($relative);
                $tmp = tempnam(sys_get_temp_dir(), 'mc_') . $extension;
                if (! copy($absolute, $tmp)) {
                    Log::channel('bot')->error('Failed to copy local file to temp', [
                        'absolute' => $absolute,
                        'tmp' => $tmp,
                    ]);
                    return null;
                }
                return $tmp;
            }

            $tmp = tempnam(sys_get_temp_dir(), 'mc_') . $extension;
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

    /**
     * Extract the file extension from a URL (e.g., ".ogg", ".jpg").
     */
    protected function extractExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! $path) {
            return '';
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return $ext !== '' ? '.' . $ext : '';
    }

    protected function publicUrlToRelativePath(string $publicUrl): ?string
    {
        if (str_contains($publicUrl, '/storage/')) {
            return substr($publicUrl, strpos($publicUrl, '/storage/') + strlen('/storage/'));
        }
        return null;
    }
}
