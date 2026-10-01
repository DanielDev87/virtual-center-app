<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Services\LocalRichTextImageStorageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LocalRichTextImageStorageServiceTest extends TestCase
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
        $service = new LocalRichTextImageStorageService();

        $this->assertSame(
            $this->normalizePath(storage_path('app/private/ticket-rich-text-images')),
            $this->normalizePath($service->resolveBasePath())
        );
    }

    /** @test */
    public function store_binary_writes_file_and_sanitizes_extension()
    {
        $customRoot = storage_path('framework/testing/richtext-unit-' . uniqid());
        AppSetting::setValue('rich_text_image_storage_root_path', $customRoot);
        $this->cleanupPaths[] = $customRoot;

        $service = new LocalRichTextImageStorageService();

        $result = $service->storeBinary('raw-binary-content', 'P!N@G', 'TK-55');

        $this->assertSame($this->normalizePath($customRoot), $this->normalizePath($result['base_path']));
        $this->assertStringStartsWith('TK-55/', $result['relative_path']);
        $this->assertStringEndsWith('.png', strtolower($result['absolute_path']));
        $this->assertFileExists($result['absolute_path']);
    }

    /** @test */
    public function resolve_absolute_path_normalizes_directory_separators()
    {
        $customRoot = storage_path('framework/testing/richtext-unit-' . uniqid());
        AppSetting::setValue('rich_text_image_storage_root_path', $customRoot);
        $this->cleanupPaths[] = $customRoot;

        $service = new LocalRichTextImageStorageService();

        $absolute = $service->resolveAbsolutePath('55\\a/b/file.png');

        $this->assertSame(
            $this->normalizePath($customRoot . DIRECTORY_SEPARATOR . '55' . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'file.png'),
            $this->normalizePath($absolute)
        );
    }
}
