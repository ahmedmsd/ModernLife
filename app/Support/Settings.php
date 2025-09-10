<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use App\Models\SystemSetting;

class Settings
{
public static function all(): array
{
return Cache::rememberForever('system_settings_all', function () {
return SystemSetting::query()
->get()
->pluck('setting_value', 'setting_key')
->toArray();
});
}

public static function get(string $key, $default = null)
{
return self::all()[$key] ?? $default;
}

public static function forget(): void
{
Cache::forget('system_settings_all');
}
}
