<?php

namespace App\Observers;

use App\Models\SystemSetting;

class SystemSettingObserver
{
    /**
     * Clear cache when a setting is updated
     */
    public function updated(SystemSetting $setting): void
    {
        \Illuminate\Support\Facades\Cache::forget("setting.{$setting->setting_key}");
    }

    /**
     * Clear cache when a setting is deleted
     */
    public function deleted(SystemSetting $setting): void
    {
        \Illuminate\Support\Facades\Cache::forget("setting.{$setting->setting_key}");
    }

    /**
     * Clear cache when a setting is created
     */
    public function created(SystemSetting $setting): void
    {
        // Cache will be populated on first access, no need to clear
    }
}

