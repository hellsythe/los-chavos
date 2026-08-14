<?php

namespace App\Observers;

use App\Models\ServiceChatbotInfo;
use App\Services\AI\QdrantService;
use App\Support\QdrantSync;
use Illuminate\Support\Facades\Log;

class ServiceChatbotInfoObserver
{
    public function __construct(protected QdrantService $qdrant)
    {
    }

    public function saved(ServiceChatbotInfo $info): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        if ((int) $info->status === ServiceChatbotInfo::STATUS_DELETED) {
            $this->qdrant->deleteService((int) $info->id);
            return;
        }

        try {
            $this->qdrant->upsertService($info);
        } catch (\Throwable $e) {
            Log::error('Qdrant upsert service failed', [
                'service_id' => $info->service_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(ServiceChatbotInfo $info): void
    {
        if (QdrantSync::bypass()) {
            return;
        }

        try {
            $this->qdrant->deleteService((int) $info->id);
        } catch (\Throwable $e) {
            Log::error('Qdrant delete service failed', [
                'service_id' => $info->service_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
