<?php

namespace App\Services\AI;

use App\Models\School;
use App\Models\Uniform;
use Illuminate\Support\Facades\Log;
use Qdrant\Endpoints\Collections;
use Qdrant\Endpoints\Collections\Points;
use Qdrant\Exception\InvalidArgumentException;
use Qdrant\Models\PointStruct;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\Points\QueryRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;

class QdrantService
{
    protected Qdrant $client;

    protected EmbeddingService $embeddings;

    protected string $host;

    protected ?string $apiKey;

    protected string $schoolsCollection;

    protected string $uniformsCollection;

    protected int $vectorSize;

    protected string $distance;

    public function __construct(?Qdrant $client = null, ?EmbeddingService $embeddings = null)
    {
        $this->host = (string) config('qdrant.host');
        $this->apiKey = config('qdrant.api_key') ?: null;
        $this->schoolsCollection = (string) config('qdrant.collections.schools');
        $this->uniformsCollection = (string) config('qdrant.collections.uniforms');
        $this->vectorSize = (int) config('qdrant.vector_size');
        $this->distance = (string) config('qdrant.distance');

        $this->client = $client ?? $this->buildClient();
        $this->embeddings = $embeddings ?? EmbeddingService::make();
    }

    public static function make(): self
    {
        return new self();
    }

    protected function buildClient(): Qdrant
    {
        $parsed = parse_url($this->host);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? 'localhost';
        $port = $parsed['port'] ?? ($scheme === 'https' ? 443 : 80);

        $config = new \Qdrant\Config($this->host, (int) $port);
        if ($this->apiKey) {
            $config->setApiKey($this->apiKey);
        }

        $psrClient = new \Http\Discovery\Psr18Client();
        $transport = new \Qdrant\Http\Transport($psrClient, $config);

        return new Qdrant($transport);
    }

    public function schoolsCollection(): string
    {
        return $this->schoolsCollection;
    }

    public function uniformsCollection(): string
    {
        return $this->uniformsCollection;
    }

    /**
     * Create the collections if they don't exist with the proper vector configuration.
     */
    public function ensureCollections(): void
    {
        $list = $this->client->collections()->list();
        $existing = collect($list['result']['collections'] ?? [])
            ->pluck('name')
            ->all();

        foreach ([$this->schoolsCollection, $this->uniformsCollection] as $name) {
            if (in_array($name, $existing, true)) {
                continue;
            }

            $params = new VectorParams($this->vectorSize, $this->distance);
            $create = new CreateCollection();
            $create->addVector($params);

            $this->client->collections($name)->create($create);

            $this->ensurePayloadIndexes($name);
        }
    }

    protected function ensurePayloadIndexes(string $collection): void
    {
        $indexes = [
            ['field' => 'type', 'type' => 'keyword'],
            ['field' => 'school_id', 'type' => 'integer'],
            ['field' => 'nivel_educativo', 'type' => 'keyword'],
        ];

        foreach ($indexes as $idx) {
            try {
                $createIndex = new \Qdrant\Models\Request\CreateIndex($idx['field'], $idx['type']);
                $this->client->collections($collection)->index()->create($createIndex);
            } catch (\Throwable $e) {
                Log::warning('Qdrant index creation skipped', [
                    'collection' => $collection,
                    'field' => $idx['field'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Build the text representation used for embedding a school.
     */
    public function schoolEmbeddingText(School $school): string
    {
        $parts = array_filter([
            $school->name,
            $school->nivel_educativo ? 'Nivel: '.$school->nivel_educativo : null,
            $school->type ? 'Tipo: '.$school->type : null,
            $school->location,
            $school->colonia,
            $school->city,
        ]);

        return implode('. ', $parts);
    }

    /**
     * Build the text representation used for embedding a uniform.
     */
    public function uniformEmbeddingText(Uniform $uniform): string
    {
        $school = $uniform->school;

        $parts = [
            $uniform->name,
            $school ? 'Escuela: '.$school->name : null,
            $school && $school->nivel_educativo ? 'Nivel: '.$school->nivel_educativo : null,
            $uniform->description,
        ];

        return implode("\n", array_filter($parts));
    }

    public function upsertSchool(School $school): void
    {
        $this->upsertPoint(
            $this->schoolsCollection,
            $school->id,
            $this->schoolEmbeddingText($school),
            $this->schoolPayload($school),
        );
    }

    public function upsertUniform(Uniform $uniform): void
    {
        $school = $uniform->school;

        $this->upsertPoint(
            $this->uniformsCollection,
            $uniform->id,
            $this->uniformEmbeddingText($uniform),
            $this->uniformPayload($uniform, $school),
        );
    }

    protected function schoolPayload(School $school): array
    {
        return [
            'type' => 'school',
            'id' => (int) $school->id,
            'name' => (string) $school->name,
            'location' => (string) $school->location,
            'colonia' => $school->colonia,
            'city' => (string) $school->city,
            'type_label' => $school->type,
            'nivel_educativo' => $school->nivel_educativo,
            'status' => (int) $school->status,
        ];
    }

    protected function uniformPayload(Uniform $uniform, ?School $school): array
    {
        return [
            'type' => 'uniform',
            'id' => (int) $uniform->id,
            'name' => (string) $uniform->name,
            'description' => $uniform->description,
            'school_id' => $school ? (int) $school->id : null,
            'school_name' => $school?->name,
            'school_nivel' => $school?->nivel_educativo,
            'school_city' => $school?->city,
            'status' => (int) $uniform->status,
        ];
    }

    protected function upsertPoint(string $collection, int $id, string $text, array $payload): void
    {
        $vector = $this->embeddings->embed($text);

        $points = new PointsStruct();
        $points->addPoint(new PointStruct($id, new VectorStruct($vector), $payload));

        $this->client->collections($collection)->points()->upsert($points);
    }

    public function deleteSchool(int $id): void
    {
        $this->deletePoint($this->schoolsCollection, $id);
    }

    public function deleteUniform(int $id): void
    {
        $this->deletePoint($this->uniformsCollection, $id);
    }

    public function deletePoint(string $collection, int $id): void
    {
        try {
            $this->client->collections($collection)->points()->delete([$id]);
        } catch (\Throwable $e) {
            Log::warning('Qdrant delete point failed', [
                'collection' => $collection,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function truncateCollection(string $collection): void
    {
        try {
            $this->client->collections($collection)->points()->deleteByFilter(
                new \Qdrant\Models\Filter\Filter(),
            );
        } catch (\Throwable $e) {
            Log::warning('Qdrant truncate failed', [
                'collection' => $collection,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Search both collections and return normalized chunks for prompt use.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $topK): array
    {
        $vector = $this->embeddings->embed($query);

        $schools = $this->searchCollection($this->schoolsCollection, $vector, $topK);
        $uniforms = $this->searchCollection($this->uniformsCollection, $vector, $topK);

        return array_merge($schools, $uniforms);
    }

    /**
     * @param  array<int, float>  $vector
     * @return array<int, array<string, mixed>>
     */
    protected function searchCollection(string $collection, array $vector, int $limit): array
    {
        try {
            $request = new QueryRequest();
            $request->setQuery(['nearest' => $vector]);
            $request->setLimit($limit);
            $request->setWithPayload(true);

            $response = $this->client->collections($collection)->points()->query()->query($request);
            $result = $response['result'] ?? [];
            $points = $result['points'] ?? (isset($result['id']) ? [$result] : []);

            return array_map(function ($point) {
                $payload = $point['payload'] ?? [];
                $type = $payload['type'] ?? 'item';
                $id = $payload['id'] ?? $point['id'] ?? null;
                $label = $payload['name'] ?? ('#'.$id);
                $text = $this->payloadToText($type, $payload);

                return [
                    'type' => $type,
                    'id' => $id,
                    'label' => $label,
                    'text' => $text,
                    'score' => $point['score'] ?? null,
                    'payload' => $payload,
                ];
            }, $points);
        } catch (\Throwable $e) {
            Log::error('Qdrant search failed', [
                'collection' => $collection,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function payloadToText(string $type, array $payload): string
    {
        if ($type === 'school') {
            $parts = [
                'Escuela: '.($payload['name'] ?? ''),
                'Nivel: '.($payload['nivel_educativo'] ?? 'N/D'),
                'Tipo: '.($payload['type_label'] ?? 'N/D'),
                'Ubicación: '.trim(($payload['location'] ?? '').', '.($payload['colonia'] ?? '').', '.($payload['city'] ?? ''), ' ,'),
            ];
            return implode("\n", array_filter($parts));
        }

        if ($type === 'uniform') {
            $parts = [
                'Uniforme: '.($payload['name'] ?? ''),
                'Escuela: '.($payload['school_name'] ?? 'N/D'),
                'Nivel: '.($payload['school_nivel'] ?? 'N/D'),
            ];
            if (! empty($payload['description'])) {
                $parts[] = 'Descripción: '.$payload['description'];
            }
            return implode("\n", $parts);
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public function countSchools(): int
    {
        return $this->countCollection($this->schoolsCollection);
    }

    public function countUniforms(): int
    {
        return $this->countCollection($this->uniformsCollection);
    }

    protected function countCollection(string $collection): int
    {
        try {
            $response = $this->client->collections($collection)->points()->count();

            return (int) ($response['result']['count'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
