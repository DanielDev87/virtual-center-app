<?php

namespace Tests\Unit;

use App\Http\Controllers\TechnicalBrandingSettingsController;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Tests\TestCase;

class TechnicalBrandingSettingsControllerUnitTest extends TestCase
{
    use DatabaseTransactions;

    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    /** @test */
    public function rules_include_expected_branding_constraints()
    {
        $controller = new TechnicalBrandingSettingsController();

        $rules = $this->invokePrivate($controller, 'rules');

        $this->assertArrayHasKey('branding_app_name', $rules);
        $this->assertArrayHasKey('branding_logo_path', $rules);
        $this->assertArrayHasKey('branding_primary_color', $rules);
        $this->assertArrayHasKey('mail_from_name', $rules);
        $this->assertArrayHasKey('mail_from_address', $rules);
        $this->assertArrayHasKey('mail_smtp_host', $rules);
        $this->assertArrayHasKey('mail_smtp_port', $rules);
        $this->assertArrayHasKey('mail_smtp_encryption', $rules);
    }

    /** @test */
    public function file_rules_include_expected_upload_constraints()
    {
        $controller = new TechnicalBrandingSettingsController();

        $rules = $this->invokePrivate($controller, 'fileRules');

        $this->assertArrayHasKey('branding_logo_file', $rules);
        $this->assertArrayHasKey('branding_footer_logo_file', $rules);
        $this->assertArrayHasKey('branding_favicon_file', $rules);
    }

    /** @test */
    public function import_returns_error_when_json_format_is_invalid()
    {
        $controller = new TechnicalBrandingSettingsController();

        $file = UploadedFile::fake()->createWithContent('branding.json', '{"bad":"format"}');
        $request = Request::create('/technical/branding-settings/import', 'POST', [], [], ['branding_file' => $file]);

        $response = $controller->import($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertNotNull(session('error'));
        $this->assertStringContainsString('formato de branding valido', session('error'));
    }

    /** @test */
    public function import_persists_only_allowed_settings_from_valid_payload()
    {
        $controller = new TechnicalBrandingSettingsController();

        $payload = json_encode([
            'format' => 'app-branding-v1',
            'settings' => [
                'branding_app_name' => 'Nombre Nuevo',
                'branding_short_name' => 'NN',
                'branding_logo_path' => 'img/logo.png',
                'branding_footer_logo_path' => 'img/footer.png',
                'branding_favicon_path' => 'img/favicon.png',
                'branding_primary_color' => '#112233',
                'branding_secondary_color' => '#445566',
                'mail_from_name' => 'Mesa de Ayuda ULA',
                'mail_from_address' => 'no-reply@ula.edu',
                'mail_smtp_host' => 'smtp.office365.com',
                'mail_smtp_port' => '587',
                'mail_smtp_encryption' => 'tls',
                'unexpected_key' => 'ignored',
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('branding-valid.json', $payload);
        $request = Request::create('/technical/branding-settings/import', 'POST', [], [], ['branding_file' => $file]);

        $response = $controller->import($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Nombre Nuevo', AppSetting::getValue('branding_app_name'));
        $this->assertSame('#112233', AppSetting::getValue('branding_primary_color'));
        $this->assertSame('Mesa de Ayuda ULA', AppSetting::getValue('mail_from_name'));
        $this->assertSame('no-reply@ula.edu', AppSetting::getValue('mail_from_address'));
        $this->assertSame('smtp.office365.com', AppSetting::getValue('mail_smtp_host'));
        $this->assertSame('587', AppSetting::getValue('mail_smtp_port'));
        $this->assertSame('tls', AppSetting::getValue('mail_smtp_encryption'));
        $this->assertNull(AppSetting::where('setting_key', 'unexpected_key')->value('setting_value'));
    }

    /** @test */
    public function store_branding_image_returns_storage_public_path()
    {
        Storage::fake('public');

        $controller = new TechnicalBrandingSettingsController();
        $file = UploadedFile::fake()->createWithContent('logo.png', 'png-content');

        $stored = $this->invokePrivate($controller, 'storeBrandingImage', [$file, 'logo']);

        $this->assertStringStartsWith('storage/branding/logo-', $stored);
        $this->assertStringEndsWith('.png', $stored);

        $relative = str_replace('storage/', '', $stored);
        Storage::disk('public')->assertExists($relative);
    }
}
