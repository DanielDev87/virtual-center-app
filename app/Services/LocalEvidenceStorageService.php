<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LocalEvidenceStorageService
{
    private const SETTING_STORAGE_PATH = 'evidence_storage_root_path';

    public function resolveBasePath(): string
    {
        $configuredPath = AppSetting::getValue(self::SETTING_STORAGE_PATH);

        if (!empty($configuredPath)) {
            return $this->normalizePath($configuredPath);
        }

        return storage_path('app/private/ticket-evidences');
    }

    public function store(UploadedFile $file, string $ticketNumber): array
    {
        $basePath = $this->resolveBasePath();
        $ticketFolder = trim($ticketNumber);
        $targetDirectory = $basePath . DIRECTORY_SEPARATOR . $ticketFolder;

        File::ensureDirectoryExists($targetDirectory);

        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $safeName = $safeName !== '' ? $safeName : 'archivo';
        $storedFileName = now()->format('Ymd_His') . '_' . Str::random(8) . '_' . $safeName;
        if (!empty($extension)) {
            $storedFileName .= '.' . $extension;
        }

        $file->move($targetDirectory, $storedFileName);

        return [
            'base_path' => $basePath,
            'relative_path' => $ticketFolder . '/' . $storedFileName,
            'absolute_path' => $targetDirectory . DIRECTORY_SEPARATOR . $storedFileName,
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
