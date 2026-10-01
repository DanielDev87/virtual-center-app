<?php

namespace Tests\Unit;

use App\Services\GoogleDriveStorageService;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class GoogleDriveStorageServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function is_configured_returns_false_when_service_disabled_or_missing_credentials()
    {
        Config::set('services.google_drive.enabled', false);
        Config::set('services.google_drive.client_id', null);
        Config::set('services.google_drive.client_secret', null);
        Config::set('services.google_drive.refresh_token', null);

        $service = new GoogleDriveStorageService();

        $this->assertFalse($service->isConfigured());
    }

    /** @test */
    public function is_configured_returns_true_when_enabled_and_credentials_exist()
    {
        Config::set('services.google_drive.enabled', true);
        Config::set('services.google_drive.client_id', 'client-id');
        Config::set('services.google_drive.client_secret', 'client-secret');
        Config::set('services.google_drive.refresh_token', 'refresh-token');

        $service = new GoogleDriveStorageService();

        $this->assertTrue($service->isConfigured());
    }

    /** @test */
    public function upload_evidence_returns_null_when_not_configured()
    {
        Config::set('services.google_drive.enabled', false);

        $service = new GoogleDriveStorageService();
        $file = UploadedFile::fake()->create('demo.txt', 5, 'text/plain');

        $result = $service->uploadEvidence($file);

        $this->assertNull($result);
    }

    /** @test */
    public function http_options_respects_timeout_and_verify_ssl_flag()
    {
        Config::set('services.google_drive.verify_ssl', false);

        $service = new GoogleDriveStorageService();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('httpOptions');
        $method->setAccessible(true);

        $result = $method->invoke($service, 45);

        $this->assertSame(45, $result['timeout']);
        $this->assertFalse($result['verify']);
    }

    /** @test */
    public function make_file_public_calls_permissions_endpoint_when_request_succeeds()
    {
        $service = new GoogleDriveStorageService();

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->once()
            ->with(
                'https://www.googleapis.com/drive/v3/files/file-123/permissions',
                Mockery::on(function (array $options) {
                    return ($options['headers']['Authorization'] ?? null) === 'Bearer token-123'
                        && ($options['headers']['Content-Type'] ?? null) === 'application/json'
                        && ($options['query']['fields'] ?? null) === 'id'
                        && ($options['json']['type'] ?? null) === 'anyone'
                        && ($options['json']['role'] ?? null) === 'reader';
                })
            )
            ->andReturnSelf();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('makeFilePublic');
        $method->setAccessible(true);

        $method->invoke($service, 'file-123', 'token-123', $client);

        $this->assertTrue(true);
    }

    /** @test */
    public function make_file_public_swallows_exceptions_without_throwing()
    {
        $service = new GoogleDriveStorageService();

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->once()
            ->andThrow(new \RuntimeException('permission error'));

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('makeFilePublic');
        $method->setAccessible(true);

        $method->invoke($service, 'file-123', 'token-123', $client);

        $this->assertTrue(true);
    }
}
