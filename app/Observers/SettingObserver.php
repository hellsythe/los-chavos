<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\BusinessInfoService;

class SettingObserver
{
    public function saved(Setting $setting): void
    {
        if (in_array($setting->name, [
            BusinessInfoService::HOURS_KEY,
            BusinessInfoService::HOLIDAYS_KEY,
            BusinessInfoService::INFO_KEY,
        ], true)) {
            app(BusinessInfoService::class)->flushCache();
        }
    }
}
