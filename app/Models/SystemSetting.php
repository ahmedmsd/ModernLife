<?php

// app/Models/SystemSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'is_public',
        'description',
    ];

    public $timestamps = true;

    public static function get(string $key, $default = null) {
        $row = static::query()->where('setting_key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function put(string $key, $value): void {
        static::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
    }

}

