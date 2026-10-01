<?php

namespace Tests\Unit;

use App\Http\Controllers\TechnicalStorageSettingsController;
use App\Models\AppSetting;
use App\Services\LocalEvidenceStorageService;
use App\Services\LocalRichTextImageStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class TechnicalStorageSettingsControllerUnitTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    /** @test */
    public function set_setting_unless_blank_does_not_overwrite_value_with_empty_string()
    {
        AppSetting::setValue('custom_storage_key', 'existing-value');

        $controller = new TechnicalStorageSettingsController();
        $this->invokePrivate($controller, 'setSettingUnlessBlank', ['custom_storage_key', '   ']);

        $this->assertSame('existing-value', AppSetting::getValue('custom_storage_key'));
    }

    /** @test */
    public function set_setting_unless_blank_updates_value_when_non_blank_is_provided()
    {
        AppSetting::setValue('custom_storage_secret', 'old-secret');

        $controller = new TechnicalStorageSettingsController();
        $this->invokePrivate($controller, 'setSettingUnlessBlank', ['custom_storage_secret', 'new-secret']);

        $this->assertSame('new-secret', AppSetting::getValue('custom_storage_secret'));
    }

    /** @test */
    public function update_persists_provider_and_paths_into_app_settings()
    {
        $controller = new TechnicalStorageSettingsController();

        $evidencePath = storage_path('framework/testing/storage-settings-evidence-' . uniqid());
        $richTextPath = storage_path('framework/testing/storage-settings-richtext-' . uniqid());

        $request = Request::create('/technical/storage-settings', 'PUT', [
            'evidence_storage_provider' => 'filesystem',
            'evidence_storage_root_path' => $evidencePath,
            'rich_text_image_storage_root_path' => $richTextPath,
            'custom_storage_bucket' => 'bucket-x',
            'custom_storage_region' => 'region-x',
            'custom_storage_key' => 'key-x',
            'custom_storage_secret' => 'secret-x',
            'custom_storage_endpoint' => 'https://endpoint.test',
            'custom_storage_url' => 'https://cdn.test',
            'custom_storage_prefix' => 'tickets/custom',
            'custom_storage_visibility' => 'private',
            'custom_storage_use_path_style' => '1',
        ]);

        $localEvidence = Mockery::mock(LocalEvidenceStorageService::class);
        $localEvidence->shouldReceive('resolveBasePath')->andReturn($evidencePath);

        $localRichText = Mockery::mock(LocalRichTextImageStorageService::class);
        $localRichText->shouldReceive('resolveBasePath')->andReturn($richTextPath);

        $response = $controller->update($request, $localEvidence, $localRichText);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('filesystem', AppSetting::getValue('evidence_storage_provider'));
        $this->assertSame($evidencePath, AppSetting::getValue('evidence_storage_root_path'));
        $this->assertSame($richTextPath, AppSetting::getValue('rich_text_image_storage_root_path'));
        $this->assertSame('tickets/custom', AppSetting::getValue('custom_storage_prefix'));
        $this->assertSame('1', AppSetting::getValue('custom_storage_use_path_style'));
    }

    /** @test */
    public function validate_storage_path_input_accepts_absolute_and_relative_paths()
    {
        $controller = new TechnicalStorageSettingsController();

        $windowsPath = 'C:/srv/app/evidencias';
        $relativePath = 'storage/app/private/ticket-evidences';

        $validatedWindows = $this->invokePrivate($controller, 'validateStoragePathInput', [$windowsPath, 'evidence_storage_root_path']);
        $validatedRelative = $this->invokePrivate($controller, 'validateStoragePathInput', [$relativePath, 'rich_text_image_storage_root_path']);

        $this->assertSame($windowsPath, $validatedWindows);
        $this->assertSame($relativePath, $validatedRelative);
    }

    /** @test */
    public function validate_storage_path_input_rejects_parent_directory_segments()
    {
        $this->expectException(ValidationException::class);

        $controller = new TechnicalStorageSettingsController();
        $this->invokePrivate($controller, 'validateStoragePathInput', ['storage/../secrets', 'evidence_storage_root_path']);
    }
}
