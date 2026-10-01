<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Services\CustomObjectStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class CustomObjectStorageServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function is_configured_returns_false_when_required_settings_are_missing()
    {
        AppSetting::setValue('custom_storage_bucket', '');
        AppSetting::setValue('custom_storage_region', '');
        AppSetting::setValue('custom_storage_key', '');
        AppSetting::setValue('custom_storage_secret', '');

        $service = new CustomObjectStorageService();

        $this->assertFalse($service->isConfigured());
    }

    /** @test */
    public function is_configured_returns_true_when_required_settings_exist()
    {
        AppSetting::setValue('custom_storage_bucket', 'bucket-test');
        AppSetting::setValue('custom_storage_region', 'us-east-1');
        AppSetting::setValue('custom_storage_key', 'key-test');
        AppSetting::setValue('custom_storage_secret', 'secret-test');

        $service = new CustomObjectStorageService();

        $this->assertTrue($service->isConfigured());
    }

    /** @test */
    public function upload_evidence_returns_null_when_service_is_not_configured()
    {
        AppSetting::setValue('custom_storage_bucket', '');

        $service = new CustomObjectStorageService();
        $file = UploadedFile::fake()->create('demo.pdf', 20, 'application/pdf');

        $result = $service->uploadEvidence($file, 'T-001');

        $this->assertNull($result);
    }

    /** @test */
    public function build_disk_uses_expected_s3_configuration_values()
    {
        AppSetting::setValue('custom_storage_bucket', 'bucket-one');
        AppSetting::setValue('custom_storage_region', 'us-west-2');
        AppSetting::setValue('custom_storage_key', 'key-one');
        AppSetting::setValue('custom_storage_secret', 'secret-one');
        AppSetting::setValue('custom_storage_endpoint', 'https://s3.test.local');
        AppSetting::setValue('custom_storage_url', 'https://cdn.test.local');
        AppSetting::setValue('custom_storage_use_path_style', '1');

        $service = new CustomObjectStorageService();

        $capturedConfig = null;
        Storage::shouldReceive('build')
            ->once()
            ->with(Mockery::on(function (array $config) use (&$capturedConfig) {
                $capturedConfig = $config;
                return true;
            }))
            ->andReturn(Mockery::mock());

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildDisk');
        $method->setAccessible(true);
        $method->invoke($service);

        $this->assertIsArray($capturedConfig);
        $this->assertSame('s3', $capturedConfig['driver']);
        $this->assertSame('key-one', $capturedConfig['key']);
        $this->assertSame('secret-one', $capturedConfig['secret']);
        $this->assertSame('us-west-2', $capturedConfig['region']);
        $this->assertSame('bucket-one', $capturedConfig['bucket']);
        $this->assertSame('https://s3.test.local', $capturedConfig['endpoint']);
        $this->assertSame('https://cdn.test.local', $capturedConfig['url']);
        $this->assertTrue($capturedConfig['use_path_style_endpoint']);
        $this->assertFalse($capturedConfig['throw']);
    }

    /** @test */
    public function upload_evidence_returns_payload_with_public_url_when_upload_succeeds()
    {
        AppSetting::setValue('custom_storage_bucket', 'bucket-one');
        AppSetting::setValue('custom_storage_region', 'us-west-2');
        AppSetting::setValue('custom_storage_key', 'key-one');
        AppSetting::setValue('custom_storage_secret', 'secret-one');
        AppSetting::setValue('custom_storage_prefix', 'tickets/evidences');
        AppSetting::setValue('custom_storage_visibility', 'public');

        $service = new CustomObjectStorageService();
        $file = UploadedFile::fake()->create('Documento prueba.pdf', 10, 'application/pdf');

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('put')
            ->once()
            ->with(
                Mockery::on(fn ($key) => str_contains($key, 'tickets/evidences/T-100/')),
                Mockery::type('resource'),
                ['visibility' => 'public']
            )
            ->andReturn(true);
        $diskMock->shouldReceive('url')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn('https://cdn.test.local/fake-file.pdf');

        Storage::shouldReceive('build')->once()->andReturn($diskMock);

        $result = $service->uploadEvidence($file, 'T-100');

        $this->assertIsArray($result);
        $this->assertStringContainsString('tickets/evidences/T-100/', $result['path']);
        $this->assertSame('application/pdf', $result['mime_type']);
        $this->assertNotNull($result['size']);
        $this->assertSame('https://cdn.test.local/fake-file.pdf', $result['url']);
    }

    /** @test */
    public function upload_evidence_returns_null_when_disk_put_fails()
    {
        AppSetting::setValue('custom_storage_bucket', 'bucket-one');
        AppSetting::setValue('custom_storage_region', 'us-west-2');
        AppSetting::setValue('custom_storage_key', 'key-one');
        AppSetting::setValue('custom_storage_secret', 'secret-one');

        $service = new CustomObjectStorageService();
        $file = UploadedFile::fake()->create('demo.pdf', 10, 'application/pdf');

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('put')->once()->andReturn(false);

        Storage::shouldReceive('build')->once()->andReturn($diskMock);

        $result = $service->uploadEvidence($file, 'T-101');

        $this->assertNull($result);
    }

    /** @test */
    public function download_returns_null_when_file_does_not_exist()
    {
        $service = new CustomObjectStorageService();

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('exists')->once()->with('tickets/evidences/T-100/file.pdf')->andReturn(false);

        Storage::shouldReceive('build')->once()->andReturn($diskMock);

        $result = $service->download('tickets/evidences/T-100/file.pdf', 'file.pdf');

        $this->assertNull($result);
    }

    /** @test */
    public function download_returns_null_when_stream_cannot_be_opened()
    {
        $service = new CustomObjectStorageService();

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('exists')->once()->with('tickets/evidences/T-100/file.pdf')->andReturn(true);
        $diskMock->shouldReceive('readStream')->once()->with('tickets/evidences/T-100/file.pdf')->andReturn(false);

        Storage::shouldReceive('build')->once()->andReturn($diskMock);

        $result = $service->download('tickets/evidences/T-100/file.pdf', 'file.pdf');

        $this->assertNull($result);
    }

    /** @test */
    public function download_returns_streamed_response_when_file_exists()
    {
        $service = new CustomObjectStorageService();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, 'contenido de prueba');
        rewind($stream);

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('exists')->once()->with('tickets/evidences/T-100/file.pdf')->andReturn(true);
        $diskMock->shouldReceive('readStream')->once()->with('tickets/evidences/T-100/file.pdf')->andReturn($stream);

        Storage::shouldReceive('build')->once()->andReturn($diskMock);

        $result = $service->download('tickets/evidences/T-100/file.pdf', 'file.pdf');

        $this->assertInstanceOf(StreamedResponse::class, $result);
        $this->assertStringContainsString('file.pdf', (string) $result->headers->get('content-disposition'));
    }
}
