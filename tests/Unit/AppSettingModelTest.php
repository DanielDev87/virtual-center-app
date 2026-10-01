<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppSettingModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function get_value_returns_default_when_setting_does_not_exist()
    {
        $this->assertSame('fallback', AppSetting::getValue('missing_key_for_test', 'fallback'));
    }

    /** @test */
    public function get_value_returns_default_when_setting_value_is_empty_string()
    {
        AppSetting::create([
            'setting_key' => 'empty_value_key',
            'setting_value' => '',
        ]);

        $this->assertSame('fallback', AppSetting::getValue('empty_value_key', 'fallback'));
    }

    /** @test */
    public function set_value_creates_new_setting_when_key_is_missing()
    {
        AppSetting::setValue('unit_setting_key', 'first-value');

        $this->assertDatabaseHas('app_settings', [
            'setting_key' => 'unit_setting_key',
            'setting_value' => 'first-value',
        ]);
    }

    /** @test */
    public function set_value_updates_existing_setting_value()
    {
        AppSetting::create([
            'setting_key' => 'updatable_setting_key',
            'setting_value' => 'old',
        ]);

        AppSetting::setValue('updatable_setting_key', 'new');

        $this->assertDatabaseHas('app_settings', [
            'setting_key' => 'updatable_setting_key',
            'setting_value' => 'new',
        ]);
    }
}
