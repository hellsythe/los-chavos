<?php

namespace App\Services\AI;

use App\Models\School;
use App\Models\ServiceChatbotInfo;
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

    protected string $servicesCollection;

    protected int $vectorSize;

    protected string $distance;

    public function __construct(?Qdrant $client = null, ?EmbeddingService $embeddings = null)
    {
        $this->host = (string) config('qdrant.host');
        $this->apiKey = config('qdrant.api_key') ?: null;
        $this->schoolsCollection = (string) config('qdrant.collections.schools');
        $this->uniformsCollection = (string) config('qdrant.collections.uniforms');
        $this->servicesCollection = (string) config('qdrant.collections.services');
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

    public function servicesCollection(): string
    {
        return $this->servicesCollection;
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

        foreach ([$this->schoolsCollection, $this->uniformsCollection, $this->servicesCollection] as $name) {
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

    public function upsertService(ServiceChatbotInfo $info): void
    {
        $this->upsertPoint(
            $this->servicesCollection,
            $info->id,
            $this->serviceEmbeddingText($info),
            $this->servicePayload($info),
        );
    }

    public function deleteService(int $id): void
    {
        $this->deletePoint($this->servicesCollection, $id);
    }

    public function serviceEmbeddingText(ServiceChatbotInfo $info): string
    {
        $parts = [
            $info->name,
            $info->description,
            $info->notes,
        ];

        return implode("\n", array_filter($parts));
    }

    protected function servicePayload(ServiceChatbotInfo $info): array
    {
        return [
            'type' => 'service',
            'id' => (int) $info->id,
            'name' => $info->name,
            'description' => $info->description,
            'notes' => $info->notes,
            'status' => (int) $info->status,
        ];
    }

    public function countServices(): int
    {
        return $this->countCollection($this->servicesCollection);
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
     * Search all collections and return normalized chunks for prompt use.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $topK): array
    {
        $vector = $this->embeddings->embed($query);

        $schools = $this->searchCollection($this->schoolsCollection, $vector, $topK);
        $uniforms = $this->searchCollection($this->uniformsCollection, $vector, $topK);
        $services = $this->searchCollection($this->servicesCollection, $vector, $topK);

        $detectedSchoolIds = $this->detectSchoolIds($query, $schools);
        if (! empty($detectedSchoolIds)) {
            $schoolUniforms = $this->getUniformsBySchoolIds($detectedSchoolIds);
            $existingIds = array_column($uniforms, 'id');
            foreach ($schoolUniforms as $uni) {
                if (! in_array($uni['id'], $existingIds, true)) {
                    $uniforms[] = $uni;
                }
            }
        }

        return array_merge($schools, $uniforms, $services);
    }

    /**
     * Detect school IDs from the query by matching the school name against the query text.
     * Returns the IDs of schools whose name appears (case-insensitive) in the query.
     *
     * @param  array<int, array<string, mixed>>  $schoolsResult
     * @return array<int, int>
     */
    protected function detectSchoolIds(string $query, array $schoolsResult): array
    {
        $queryNormalized = mb_strtolower(trim($query));
        if ($queryNormalized === '') {
            return [];
        }

        $ids = [];
        foreach ($schoolsResult as $school) {
            $name = mb_strtolower($school['label'] ?? '');
            if ($name === '' || mb_strlen($name) < 3) {
                continue;
            }
            if (mb_strpos($queryNormalized, $name) !== false) {
                $payloadId = $school['payload']['id'] ?? null;
                if ($payloadId !== null) {
                    $ids[] = (int) $payloadId;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Fetch all uniforms for the given school IDs directly from the DB and normalize them
     * to the same chunk format as Qdrant results.
     *
     * @param  array<int, int>  $schoolIds
     * @return array<int, array<string, mixed>>
     */
    protected function getUniformsBySchoolIds(array $schoolIds): array
    {
        if (empty($schoolIds)) {
            return [];
        }

        $uniforms = \App\Models\Uniform::with('school')
            ->whereIn('school_id', $schoolIds)
            ->where('status', \App\Models\Uniform::STATUS_ACTIVE)
            ->get();

        $out = [];
        foreach ($uniforms as $uniform) {
            $out[] = [
                'type' => 'uniform',
                'id' => (int) $uniform->id,
                'label' => (string) $uniform->name,
                'text' => $this->payloadToText('uniform', $this->uniformPayload($uniform, $uniform->school)),
                'score' => 1.0,
                'payload' => $this->uniformPayload($uniform, $uniform->school),
            ];
        }
        return $out;
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

        if ($type === 'service') {
            $parts = [
                'Servicio: '.($payload['name'] ?? ''),
            ];
            if (! empty($payload['description'])) {
                $parts[] = 'Descripción: '.$payload['description'];
            }
            if (! empty($payload['notes'])) {
                $parts[] = 'Notas: '.$payload['notes'];
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
