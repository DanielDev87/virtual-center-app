<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $value = static::where('setting_key', $key)->value('setting_value');

        return $value !== null && $value !== '' ? $value : $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
