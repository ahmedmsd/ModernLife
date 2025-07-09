<?php

use App\Models\SystemSetting;

if (!function_exists('setting')) {
    /**
     * Get system setting by key
     */
    function setting(string $key, $default = null)
    {
        static $settingsCache = [];

        if (empty($settingsCache)) {
            $settingsCache = SystemSetting::pluck('setting_value', 'setting_key')->toArray();
        }

        return $settingsCache[$key] ?? $default;
    }
}
