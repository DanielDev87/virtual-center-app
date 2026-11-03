<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\BrandPaletteService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TechnicalBrandingSettingsController extends Controller
{
    private const SETTING_KEYS = [
        'branding_app_name',
        'branding_institution_name',
        'branding_short_name',
        'branding_logo_path',
        'branding_footer_logo_path',
        'branding_favicon_path',
        'branding_primary_color',
        'branding_secondary_color',
        'mail_from_name',
        'mail_from_address',
        'mail_smtp_host',
        'mail_smtp_port',
        'mail_smtp_encryption',
    ];

    private const DEFAULTS = [
        'branding_app_name' => 'Sistema de Soporte Universitario',
        'branding_institution_name' => 'Mesa de Servicio',
        'branding_short_name' => 'Soporte Univ.',
        'branding_logo_path' => 'img/logomsula.png',
        'branding_footer_logo_path' => 'img/LogoCampus.png',
        'branding_favicon_path' => 'img/logomsula.png',
        'branding_primary_color' => '#0280AE',
        'branding_secondary_color' => '#17a2b8',
        'mail_from_name' => 'Sistema de Soporte Universitario',
        'mail_from_address' => 'no-reply@example.com',
        'mail_smtp_host' => 'smtp.mailgun.org',
        'mail_smtp_port' => '587',
        'mail_smtp_encryption' => 'tls',
    ];

    public function edit()
    {
        $settings = [];

        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = AppSetting::getValue($key, $default) ?? $default;
        }

        return view('technical.branding-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate(array_merge($this->rules(), $this->fileRules()));

        foreach (self::SETTING_KEYS as $key) {
            $value = isset($validated[$key]) ? trim((string) $validated[$key]) : (self::DEFAULTS[$key] ?? '');
            AppSetting::setValue($key, $value);
        }

        if ($request->hasFile('branding_logo_file')) {
            AppSetting::setValue('branding_logo_path', $this->storeBrandingImage($request->file('branding_logo_file'), 'logo'));
        }

        if ($request->hasFile('branding_footer_logo_file')) {
            AppSetting::setValue('branding_footer_logo_path', $this->storeBrandingImage($request->file('branding_footer_logo_file'), 'footer-logo'));
        }

        if ($request->hasFile('branding_favicon_file')) {
            AppSetting::setValue('branding_favicon_path', $this->storeBrandingImage($request->file('branding_favicon_file'), 'favicon'));
        }

        return back()->with('success', 'Personalizacion de marca guardada correctamente.');
    }

    /**
     * Guarda la paleta de colores (HU-039). Valida el contraste antes de guardar.
     */
    public function updatePalette(Request $request)
    {
        $rules = ['palette_enabled' => 'nullable|boolean'];
        $messages = [];

        foreach (range(1, 6) as $i) {
            $rules['palette_' . $i] = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
            $messages['palette_' . $i . '.required'] = 'El color ' . $i . ' de la paleta es obligatorio.';
            $messages['palette_' . $i . '.regex'] = 'El color ' . $i . ' debe tener formato hexadecimal, por ejemplo #06141B.';
        }

        $validated = $request->validateWithBag('palette', $rules, $messages);

        $palette = array_map(fn ($i) => strtoupper($validated['palette_' . $i]), range(1, 6));
        $enabled = $request->boolean('palette_enabled');

        if ($enabled) {
            $result = BrandPaletteService::evaluate($palette);

            if (!$result['ok']) {
                $suggestionIsReadable = BrandPaletteService::evaluate($result['suggested'])['ok'];

                return back()
                    ->withInput()
                    ->withErrors(['contrast' => 'La paleta no se guardó: algunas combinaciones de texto no alcanzan el contraste mínimo WCAG AA (4.5:1).'], 'palette')
                    ->with('palette_failures', $this->failureMessages($result['failures']))
                    ->with('palette_suggested', $suggestionIsReadable ? $result['suggested'] : null);
            }
        }

        $this->savePalette($palette, $enabled);

        return back()->with('success', $enabled
            ? 'Paleta guardada y aplicada a la plataforma.'
            : 'Paleta guardada, pero desactivada: la plataforma conserva su apariencia actual.');
    }

    public function export()
    {
        $settings = [];

        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = AppSetting::getValue($key, $default) ?? $default;
        }

        $paletteConfig = BrandPaletteService::settings();
        foreach (BrandPaletteService::KEYS as $index => $key) {
            $settings[$key] = $paletteConfig['palette'][$index];
        }
        $settings[BrandPaletteService::ENABLED_KEY] = $paletteConfig['enabled'] ? '1' : '0';

        $payload = [
            'format' => 'app-branding-v1',
            'exported_at' => now()->toIso8601String(),
            'settings' => $settings,
        ];

        $filename = 'branding-settings-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'branding_file' => 'required|file|max:2048',
        ]);

        $content = file_get_contents($request->file('branding_file')->getRealPath());
        $decoded = json_decode((string) $content, true);

        if (!is_array($decoded) || !isset($decoded['settings']) || !is_array($decoded['settings'])) {
            return back()->with('error', 'El archivo no tiene un formato de branding valido.');
        }

        $filtered = [];
        foreach (self::SETTING_KEYS as $key) {
            if (array_key_exists($key, $decoded['settings'])) {
                $filtered[$key] = $decoded['settings'][$key];
            }
        }

        $validator = Validator::make($filtered, $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'El archivo contiene datos invalidos.');
        }

        // Paleta (HU-039): un archivo antiguo sin paleta restablece los valores predeterminados.
        $importPalette = BrandPaletteService::DEFAULT_PALETTE;
        $importEnabled = false;
        $hasPalette = count(array_intersect_key($decoded['settings'], array_flip(BrandPaletteService::KEYS))) > 0;

        if ($hasPalette) {
            $paletteInput = [];
            foreach (BrandPaletteService::KEYS as $index => $key) {
                $paletteInput[$key] = $decoded['settings'][$key] ?? BrandPaletteService::DEFAULT_PALETTE[$index];
            }

            $paletteValidator = Validator::make(
                $paletteInput,
                array_fill_keys(BrandPaletteService::KEYS, ['required', 'regex:/^#[0-9a-fA-F]{6}$/'])
            );

            if ($paletteValidator->fails()) {
                return back()->with('error', 'El archivo contiene una paleta de colores invalida.');
            }

            $importPalette = array_map('strtoupper', array_values($paletteInput));
            $importEnabled = (string) ($decoded['settings'][BrandPaletteService::ENABLED_KEY] ?? '0') === '1';

            if ($importEnabled && !BrandPaletteService::evaluate($importPalette)['ok']) {
                return back()->with('error', 'La paleta del archivo no cumple el contraste minimo WCAG AA (4.5:1) y no se importo.');
            }
        }

        $validated = $validator->validated();
        foreach (self::SETTING_KEYS as $key) {
            $value = isset($validated[$key]) ? trim((string) $validated[$key]) : (self::DEFAULTS[$key] ?? '');
            AppSetting::setValue($key, $value);
        }

        $this->savePalette($importPalette, $importEnabled);

        return back()->with('success', 'Personalizacion importada correctamente.');
    }

    public function asset(string $filename)
    {
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            abort(404);
        }

        $candidates = [
            public_path('storage/branding/'.$filename),
            public_path('branding/'.$filename),
            storage_path('app/public/branding/'.$filename),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        abort(404);
    }

    private function savePalette(array $palette, bool $enabled): void
    {
        foreach (array_values($palette) as $index => $color) {
            AppSetting::setValue('branding_palette_'.($index + 1), $color);
        }

        AppSetting::setValue(BrandPaletteService::ENABLED_KEY, $enabled ? '1' : '0');
    }

    private function failureMessages(array $failures): array
    {
        return array_map(
            fn ($check) => $check['label'].': '.number_format($check['ratio'], 2).':1 (mínimo 4.5:1)',
            $failures
        );
    }

    private function rules(): array
    {
        return [
            'branding_app_name' => 'required|string|max:120',
            'branding_institution_name' => 'required|string|max:120',
            'branding_short_name' => 'required|string|max:50',
            'branding_logo_path' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\.\/]+$/'],
            'branding_footer_logo_path' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\.\/]+$/'],
            'branding_favicon_path' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-\.\/]+$/'],
            'branding_primary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'branding_secondary_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'mail_from_name' => 'required|string|max:120',
            'mail_from_address' => 'required|email:rfc|max:190',
            'mail_smtp_host' => 'required|string|max:190',
            'mail_smtp_port' => 'required|integer|min:1|max:65535',
            'mail_smtp_encryption' => 'nullable|in:tls,ssl',
        ];
    }

    private function fileRules(): array
    {
        return [
            'branding_logo_file' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'branding_footer_logo_file' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'branding_favicon_file' => 'nullable|mimes:png,ico,svg,webp|max:1024',
        ];
    }

    private function storeBrandingImage(UploadedFile $file, string $prefix): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'png';
        $filename = $prefix.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$extension;

        $file->storeAs('branding', $filename, 'public');

        return 'storage/branding/'.$filename;
    }
}