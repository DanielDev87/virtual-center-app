<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Services\LocalEvidenceStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocalEvidenceStorageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private array $cleanupPaths = [];

    private function normalizePath(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanupPaths as $path) {
            if (is_dir($path)) {
                File::deleteDirectory($path);
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function resolve_base_path_uses_default_when_setting_is_missing()
    {
        $service = new LocalEvidenceStorageService();

        $this->assertSame(
            $this->normalizePath(storage_path('app/private/ticket-evidences')),
            $this->normalizePath($service->resolveBasePath())
        );
    }

    /** @test */
    public function resolve_base_path_uses_configured_relative_path_from_base_path()
    {
        AppSetting::setValue('evidence_storage_root_path', 'storage/custom-evidence');

        $service = new LocalEvidenceStorageService();

        $this->assertSame(
            $this->normalizePath(base_path('storage' . DIRECTORY_SEPARATOR . 'custom-evidence')),
            $this->normalizePath($service->resolveBasePath())
        );
    }

    /** @test */
    public function store_moves_file_and_returns_expected_paths()
    {
        $customRoot = storage_path('framework/testing/evidence-unit-' . uniqid());
        AppSetting::setValue('evidence_storage_root_path', $customRoot);
        $this->cleanupPaths[] = $customRoot;

        $service = new LocalEvidenceStorageService();
        $file = UploadedFile::fake()->create('mi evidencia.pdf', 20, 'application/pdf');

        $result = $service->store($file, 'TICKET-100');

        $this->assertSame($this->normalizePath($customRoot), $this->normalizePath($result['base_path']));
        $this->assertStringStartsWith('TICKET-100/', $result['relative_path']);
        $this->assertFileExists($result['absolute_path']);
    }

    /** @test */
    public function resolve_absolute_path_normalizes_directory_separators()
    {
        $customRoot = storage_path('framework/testing/evidence-unit-' . uniqid());
        AppSetting::setValue('evidence_storage_root_path', $customRoot);
        $this->cleanupPaths[] = $customRoot;

        $service = new LocalEvidenceStorageService();

        $absolute = $service->resolveAbsolutePath('123\\a/b/file.pdf');

        $this->assertSame(
            $this->normalizePath($customRoot . DIRECTORY_SEPARATOR . '123' . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'file.pdf'),
            $this->normalizePath($absolute)
        );
    }
}
