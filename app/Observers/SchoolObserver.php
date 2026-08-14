<?php

namespace App\Observers;

use App\Models\School;
use App\Services\AI\QdrantService;
use App\Support\QdrantSync;
use Illuminate\Support\Facades\Log;

class SchoolObserver
{
    public function __construct(protected QdrantService $qdrant)
    {
    }

    public function saved(School $school): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        if ((int) $school->status === School::STATUS_DELETED) {
            $this->qdrant->deleteSchool((int) $school->id);
            return;
        }

        try {
            $this->qdrant->upsertSchool($school);
        } catch (\Throwable $e) {
            Log::error('Qdrant upsert school failed', [
                'school_id' => $school->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(School $school): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        try {
            $this->qdrant->deleteSchool((int) $school->id);
        } catch (\Throwable $e) {
            Log::error('Qdrant delete school failed', [
                'school_id' => $school->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
