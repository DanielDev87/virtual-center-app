<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomObjectStorageService
{
    public function isConfigured(): bool
    {
        return !empty(AppSetting::getValue('custom_storage_bucket'))
            && !empty(AppSetting::getValue('custom_storage_region'))
            && !empty(AppSetting::getValue('custom_storage_key'))
            && !empty(AppSetting::getValue('custom_storage_secret'));
    }

    public function uploadEvidence(UploadedFile $file, string $ticketNumber): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $disk = $this->buildDisk();
        $prefix = trim((string) AppSetting::getValue('custom_storage_prefix', 'tickets/evidences'), '/');
        $safeBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $safeBaseName = $safeBaseName !== '' ? $safeBaseName : 'archivo';
        $extension = $file->getClientOriginalExtension();

        $fileName = now()->format('Ymd_His') . '_' . Str::random(8) . '_' . $safeBaseName;
        if (!empty($extension)) {
            $fileName .= '.' . $extension;
        }

        $key = trim($prefix . '/' . trim($ticketNumber) . '/' . $fileName, '/');

        $stream = fopen($file->getRealPath(), 'r');
        if ($stream === false) {
            return null;
        }

        try {
            $visibility = AppSetting::getValue('custom_storage_visibility', 'private') === 'public' ? 'public' : 'private';
            $result = $disk->put($key, $stream, ['visibility' => $visibility]);

            if (!$result) {
                return null;
            }

            return [
                'path' => $key,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => $visibility === 'public' ? $disk->url($key) : null,
            ];
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function download(string $filePath, string $downloadName)
    {
        $disk = $this->buildDisk();

        if (!$disk->exists($filePath)) {
            return null;
        }

        $stream = $disk->readStream($filePath);
        if ($stream === false) {
            return null;
        }

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $downloadName);
    }

    private function buildDisk()
    {
        return Storage::build([
            'driver' => 's3',
            'key' => AppSetting::getValue('custom_storage_key'),
            'secret' => AppSetting::getValue('custom_storage_secret'),
            'region' => AppSetting::getValue('custom_storage_region'),
            'bucket' => AppSetting::getValue('custom_storage_bucket'),
            'endpoint' => AppSetting::getValue('custom_storage_endpoint'),
            'use_path_style_endpoint' => AppSetting::getValue('custom_storage_use_path_style', '0') === '1',
            'url' => AppSetting::getValue('custom_storage_url'),
            'throw' => false,
        ]);
    }
}
