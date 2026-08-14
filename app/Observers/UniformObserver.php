<?php

namespace App\Observers;

use App\Models\Uniform;
use App\Services\AI\QdrantService;
use App\Support\QdrantSync;
use Illuminate\Support\Facades\Log;

class UniformObserver
{
    public function __construct(protected QdrantService $qdrant)
    {
    }

    public function saved(Uniform $uniform): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        if ((int) $uniform->status === Uniform::STATUS_DELETED) {
            $this->qdrant->deleteUniform((int) $uniform->id);
            return;
        }

        try {
            $this->qdrant->upsertUniform($uniform);
        } catch (\Throwable $e) {
            Log::error('Qdrant upsert uniform failed', [
                'uniform_id' => $uniform->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(Uniform $uniform): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        try {
            $this->qdrant->deleteUniform((int) $uniform->id);
        } catch (\Throwable $e) {
            Log::error('Qdrant delete uniform failed', [
                'uniform_id' => $uniform->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
