<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LocalRichTextImageStorageService
{
    private const SETTING_STORAGE_PATH = 'rich_text_image_storage_root_path';

    public function resolveBasePath(): string
    {
        $configuredPath = AppSetting::getValue(self::SETTING_STORAGE_PATH);

        if (!empty($configuredPath)) {
            return $this->normalizePath($configuredPath);
        }

        return storage_path('app/private/ticket-rich-text-images');
    }

    /**
     * @param string $binaryContent Datos binarios sin procesar de la imagen.
     */
    public function storeBinary(string $binaryContent, string $extension, string $ticketNumber): array
    {
        $basePath = $this->resolveBasePath();
        $ticketFolder = trim($ticketNumber);
        $targetDirectory = $basePath . DIRECTORY_SEPARATOR . $ticketFolder;

        File::ensureDirectoryExists($targetDirectory);

        $safeExtension = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($extension));
        $safeExtension = $safeExtension !== '' ? $safeExtension : 'png';
        $storedFileName = now()->format('Ymd_His') . '_' . Str::random(8) . '_img.' . $safeExtension;

        $absolutePath = $targetDirectory . DIRECTORY_SEPARATOR . $storedFileName;
        File::put($absolutePath, $binaryContent);

        return [
            'base_path' => $basePath,
            'relative_path' => $ticketFolder . '/' . $storedFileName,
            'absolute_path' => $absolutePath,
        ];
    }

    public function resolveAbsolutePath(string $relativePath): string
    {
        $normalizedRelative = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, ltrim($relativePath, '\\/'));

        return $this->resolveBasePath() . DIRECTORY_SEPARATOR . $normalizedRelative;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);

        if (preg_match('/^[A-Za-z]:\\\\|^[A-Za-z]:\//', $trimmed) === 1 || str_starts_with($trimmed, '/')) {
            return rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $trimmed), DIRECTORY_SEPARATOR);
        }

        return rtrim(base_path(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $trimmed)), DIRECTORY_SEPARATOR);
    }
}
