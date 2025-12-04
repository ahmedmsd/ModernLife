<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get system setting by key with proper caching
     */
    function setting(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return SystemSetting::where('setting_key', $key)
                ->value('setting_value') ?? $default;
        });
    }
}

if (!function_exists('setting_clear')) {
    /**
     * Clear a specific setting from cache
     */
    function setting_clear(string $key): void
    {
        Cache::forget("setting.{$key}");
    }
}

if (!function_exists('setting_clear_all')) {
    /**
     * Clear all settings from cache
     */
    function setting_clear_all(): void
    {
        $keys = SystemSetting::pluck('setting_key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
