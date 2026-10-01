<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\LocalEvidenceStorageService;
use App\Services\LocalRichTextImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class TechnicalStorageSettingsController extends Controller
{
    public function edit(LocalEvidenceStorageService $storageService, LocalRichTextImageStorageService $richTextStorageService)
    {
        $configuredProvider = AppSetting::getValue('evidence_storage_provider', 'filesystem');
        $configuredPath = AppSetting::getValue('evidence_storage_root_path', $storageService->resolveBasePath());
        $effectivePath = $storageService->resolveBasePath();
        $configuredRichTextPath = AppSetting::getValue('rich_text_image_storage_root_path', $richTextStorageService->resolveBasePath());
        $effectiveRichTextPath = $richTextStorageService->resolveBasePath();
        $customSettings = [
            'bucket' => AppSetting::getValue('custom_storage_bucket', ''),
            'region' => AppSetting::getValue('custom_storage_region', ''),
            'key' => AppSetting::getValue('custom_storage_key', ''),
            'secret' => AppSetting::getValue('custom_storage_secret', ''),
            'endpoint' => AppSetting::getValue('custom_storage_endpoint', ''),
            'url' => AppSetting::getValue('custom_storage_url', ''),
            'prefix' => AppSetting::getValue('custom_storage_prefix', 'tickets/evidences'),
            'visibility' => AppSetting::getValue('custom_storage_visibility', 'private'),
            'use_path_style' => AppSetting::getValue('custom_storage_use_path_style', '0') === '1',
        ];

        return view('technical.storage-settings.edit', compact('configuredProvider', 'configuredPath', 'effectivePath', 'configuredRichTextPath', 'effectiveRichTextPath', 'customSettings'));
    }

    public function update(Request $request, LocalEvidenceStorageService $storageService, LocalRichTextImageStorageService $richTextStorageService)
    {
        $request->validate([
            'evidence_storage_provider' => 'required|in:filesystem,google_drive,custom',
            'evidence_storage_root_path' => 'required|string|max:500',
            'rich_text_image_storage_root_path' => 'required|string|max:500',
            'custom_storage_bucket' => 'nullable|string|max:255',
            'custom_storage_region' => 'nullable|string|max:100',
            'custom_storage_key' => 'nullable|string|max:255',
            'custom_storage_secret' => 'nullable|string|max:255',
            'custom_storage_endpoint' => 'nullable|string|max:255',
            'custom_storage_url' => 'nullable|string|max:255',
            'custom_storage_prefix' => 'nullable|string|max:255',
            'custom_storage_visibility' => 'nullable|in:private,public',
            'custom_storage_use_path_style' => 'nullable|boolean',
        ]);

        $provider = $request->input('evidence_storage_provider', 'filesystem');
        $rawPath = $this->validateStoragePathInput(
            (string) $request->input('evidence_storage_root_path'),
            'evidence_storage_root_path'
        );
        $rawRichTextPath = $this->validateStoragePathInput(
            (string) $request->input('rich_text_image_storage_root_path'),
            'rich_text_image_storage_root_path'
        );

        AppSetting::setValue('evidence_storage_provider', $provider);
        AppSetting::setValue('evidence_storage_root_path', $rawPath);
        AppSetting::setValue('rich_text_image_storage_root_path', $rawRichTextPath);
        AppSetting::setValue('custom_storage_bucket', trim((string) $request->input('custom_storage_bucket', '')));
        AppSetting::setValue('custom_storage_region', trim((string) $request->input('custom_storage_region', '')));
        $this->setSettingUnlessBlank('custom_storage_key', (string) $request->input('custom_storage_key', ''));
        $this->setSettingUnlessBlank('custom_storage_secret', (string) $request->input('custom_storage_secret', ''));
        AppSetting::setValue('custom_storage_endpoint', trim((string) $request->input('custom_storage_endpoint', '')));
        AppSetting::setValue('custom_storage_url', trim((string) $request->input('custom_storage_url', '')));
        AppSetting::setValue('custom_storage_prefix', trim((string) $request->input('custom_storage_prefix', 'tickets/evidences')) ?: 'tickets/evidences');
        AppSetting::setValue('custom_storage_visibility', $request->input('custom_storage_visibility', 'private'));
        AppSetting::setValue('custom_storage_use_path_style', $request->boolean('custom_storage_use_path_style') ? '1' : '0');

        $effectivePath = $storageService->resolveBasePath();
        $effectiveRichTextPath = $richTextStorageService->resolveBasePath();
        File::ensureDirectoryExists($effectivePath);
        File::ensureDirectoryExists($effectiveRichTextPath);

        $warnings = [];

        if (!is_writable($effectivePath)) {
            $warnings[] = 'La ruta de evidencias no tiene permisos de escritura para el servidor web.';
        }

        if (!is_writable($effectiveRichTextPath)) {
            $warnings[] = 'La ruta de imagenes pegadas no tiene permisos de escritura para el servidor web.';
        }

        if (!empty($warnings)) {
            return back()
                ->withInput()
                ->with('success', 'Configuracion guardada, pero hay advertencias de permisos.')
                ->with('error', implode(' ', $warnings));
        }

        return back()->with('success', 'Configuracion de almacenamiento actualizada correctamente.');
    }

    private function setSettingUnlessBlank(string $key, string $value): void
    {
        $trimmed = trim($value);
        if ($trimmed !== '') {
            AppSetting::setValue($key, $trimmed);
        }
    }

    private function validateStoragePathInput(string $value, string $field): string
    {
        $path = trim($value);

        if ($path === '') {
            throw ValidationException::withMessages([
                $field => 'La ruta es obligatoria.',
            ]);
        }

        if (preg_match('/[\x00-\x1F]/', $path) === 1) {
            throw ValidationException::withMessages([
                $field => 'La ruta contiene caracteres no permitidos.',
            ]);
        }

        if (preg_match('/(^|[\\\\\/])\.\.([\\\\\/]|$)/', $path) === 1) {
            throw ValidationException::withMessages([
                $field => 'La ruta no puede contener segmentos "..".',
            ]);
        }

        if (preg_match('/^[A-Za-z]+:\/\//', $path) === 1) {
            throw ValidationException::withMessages([
                $field => 'La ruta debe ser de filesystem local, no una URL.',
            ]);
        }

        return $path;
    }
}
