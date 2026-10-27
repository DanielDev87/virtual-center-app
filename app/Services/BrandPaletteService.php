<?php

namespace App\Services;

use App\Models\AppSetting;

class BrandPaletteService
{
    public const DEFAULT_PALETTE = ['#06141B', '#11212D', '#253745', '#4A5C6A', '#9BA8AB', '#CCCDCF'];

    public const KEYS = [
        'branding_palette_1',
        'branding_palette_2',
        'branding_palette_3',
        'branding_palette_4',
        'branding_palette_5',
        'branding_palette_6',
    ];

    public const ENABLED_KEY = 'branding_palette_enabled';

    public const MIN_TEXT_CONTRAST = 4.5;

    /**
     * Paleta guardada. Si no hay ninguna (configuración antigua), usa los valores predeterminados y queda desactivada.
     */
    public static function settings(): array
    {
        $stored = AppSetting::whereIn('setting_key', array_merge([self::ENABLED_KEY], self::KEYS))
            ->pluck('setting_value', 'setting_key')
            ->all();

        $palette = [];
        foreach (self::KEYS as $index => $key) {
            $value = strtoupper((string) ($stored[$key] ?? ''));
            $palette[] = preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : self::DEFAULT_PALETTE[$index];
        }

        return [
            'enabled' => ($stored[self::ENABLED_KEY] ?? '0') === '1',
            'palette' => $palette,
        ];
    }

    public static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    public static function toHex(array $rgb): string
    {
        return sprintf(
            '#%02X%02X%02X',
            max(0, min(255, (int) round($rgb[0]))),
            max(0, min(255, (int) round($rgb[1]))),
            max(0, min(255, (int) round($rgb[2])))
        );
    }

    public static function mix(string $a, string $b, float $weightOfB): string
    {
        $x = self::rgb($a);
        $y = self::rgb($b);

        return self::toHex([
            $x[0] * (1 - $weightOfB) + $y[0] * $weightOfB,
            $x[1] * (1 - $weightOfB) + $y[1] * $weightOfB,
            $x[2] * (1 - $weightOfB) + $y[2] * $weightOfB,
        ]);
    }

    public static function luminance(string $hex): float
    {
        $channels = array_map(function ($value) {
            $value = $value / 255;

            return $value <= 0.03928 ? $value / 12.92 : pow(($value + 0.055) / 1.055, 2.4);
        }, self::rgb($hex));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    public static function ratio(string $a, string $b): float
    {
        $l1 = self::luminance($a);
        $l2 = self::luminance($b);

        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    public static function lightSurface(array $palette): string
    {
        return self::mix($palette[5], '#FFFFFF', 0.6);
    }

    public static function lightSecondaryBackground(array $palette): string
    {
        return self::mix($palette[5], '#000000', 0.06);
    }

    /**
     * Combinaciones de texto normal que deben cumplir WCAG AA (4.5:1).
     */
    public static function checks(array $p): array
    {
        $surface = self::lightSurface($p);
        $secondary = self::lightSecondaryBackground($p);

        $definitions = [
            ['Modo oscuro: texto principal sobre el fondo', 5, $p[5], $p[0]],
            ['Modo oscuro: texto principal sobre las tarjetas', 5, $p[5], $p[1]],
            ['Modo oscuro: texto principal sobre fondos secundarios', 5, $p[5], $p[2]],
            ['Modo oscuro: texto secundario sobre el fondo', 4, $p[4], $p[0]],
            ['Modo oscuro: texto secundario sobre las tarjetas', 4, $p[4], $p[1]],
            ['Modo claro: texto principal sobre el fondo', 0, $p[0], $p[5]],
            ['Modo claro: texto principal sobre las tarjetas', 0, $p[0], $surface],
            ['Modo claro: texto principal sobre fondos secundarios', 0, $p[0], $secondary],
            ['Modo claro: texto secundario sobre el fondo', 2, $p[2], $p[5]],
            ['Modo claro: texto secundario sobre las tarjetas', 2, $p[2], $surface],
        ];

        $checks = [];
        foreach ($definitions as [$label, $index, $foreground, $background]) {
            $ratio = self::ratio($foreground, $background);
            $checks[] = [
                'label' => $label,
                'index' => $index,
                'foreground' => $foreground,
                'background' => $background,
                'ratio' => $ratio,
                'ok' => $ratio >= self::MIN_TEXT_CONTRAST,
            ];
        }

        return $checks;
    }

    /**
     * Color más cercano al original que cumple el contraste mínimo contra el fondo.
     */
    public static function suggestColor(string $foreground, string $background, float $minimum = self::MIN_TEXT_CONTRAST): string
    {
        $best = null;
        $bestStep = PHP_INT_MAX;

        foreach (['#000000', '#FFFFFF'] as $target) {
            for ($step = 1; $step <= 50; $step++) {
                $candidate = self::mix($foreground, $target, $step / 50);

                if (self::ratio($candidate, $background) >= $minimum) {
                    if ($step < $bestStep) {
                        $best = $candidate;
                        $bestStep = $step;
                    }
                    break;
                }
            }
        }

        if ($best !== null) {
            return $best;
        }

        return self::ratio('#000000', $background) >= self::ratio('#FFFFFF', $background) ? '#000000' : '#FFFFFF';
    }

    /**
     * Evalúa la paleta. Si no cumple, propone una alternativa legible.
     */
    public static function evaluate(array $palette): array
    {
        $palette = array_values($palette);
        $checks = self::checks($palette);
        $failures = array_values(array_filter($checks, fn ($check) => !$check['ok']));

        if (empty($failures)) {
            return ['ok' => true, 'checks' => $checks, 'failures' => [], 'suggested' => $palette];
        }

        $suggested = $palette;

        for ($pass = 0; $pass < 6; $pass++) {
            $pending = array_values(array_filter(self::checks($suggested), fn ($check) => !$check['ok']));
            if (empty($pending)) {
                break;
            }

            foreach ($pending as $check) {
                $index = $check['index'];
                $suggested[$index] = self::suggestColor($suggested[$index], $check['background']);
            }
        }

        return ['ok' => false, 'checks' => $checks, 'failures' => $failures, 'suggested' => $suggested];
    }

    /**
     * Texto legible para botones del color primario: blanco o el color más oscuro de la paleta.
     */
    public static function buttonText(string $background, array $palette): string
    {
        return self::ratio('#FFFFFF', $background) >= self::ratio($palette[0], $background) ? '#FFFFFF' : $palette[0];
    }

    private static function expandHex(?string $hex, string $fallback): string
    {
        $hex = trim((string) $hex);

        if (preg_match('/^#[0-9a-fA-F]{3}$/', $hex)) {
            $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $hex) ? strtoupper($hex) : $fallback;
    }

    /**
     * CSS que aplica la paleta. Devuelve cadena vacía si la paleta no está activada.
     */
    public static function css(): string
    {
        try {
            $config = self::settings();
            if (!$config['enabled']) {
                return '';
            }

            $p = $config['palette'];
            $surface = self::lightSurface($p);
            $secondary = self::lightSecondaryBackground($p);
            $primary = self::expandHex(AppSetting::getValue('branding_primary_color', '#0280AE'), '#0280AE');
            $buttonText = self::buttonText($primary, $p);

            return
                '[data-bs-theme="dark"]{--bs-body-bg:' . $p[0] . ';--bs-body-color:' . $p[5] . ';--bs-emphasis-color:' . $p[5]
                . ';--bs-secondary-color:' . $p[4] . ';--bs-tertiary-bg:' . $p[1] . ';--bs-secondary-bg:' . $p[2]
                . ';--bs-border-color:' . $p[3] . '}'
                . '[data-bs-theme="dark"] .card,[data-bs-theme="dark"] .modal-content,[data-bs-theme="dark"] .dropdown-menu'
                . '{--bs-card-bg:' . $p[1] . ';--bs-modal-bg:' . $p[1] . ';--bs-dropdown-bg:' . $p[1] . '}'
                . '[data-bs-theme="light"]{--bs-body-bg:' . $p[5] . ';--bs-body-color:' . $p[0] . ';--bs-emphasis-color:' . $p[0]
                . ';--bs-secondary-color:' . $p[2] . ';--bs-tertiary-bg:' . $surface . ';--bs-secondary-bg:' . $secondary
                . ';--bs-border-color:' . $p[4] . '}'
                . '[data-bs-theme="light"] .card,[data-bs-theme="light"] .modal-content,[data-bs-theme="light"] .dropdown-menu'
                . '{--bs-card-bg:' . $surface . ';--bs-modal-bg:' . $surface . ';--bs-dropdown-bg:' . $surface . '}'
                . '.btn-primary{--bs-btn-color:' . $buttonText . ';--bs-btn-hover-color:' . $buttonText
                . ';--bs-btn-active-color:' . $buttonText . '}';
        } catch (\Throwable $exception) {
            return '';
        }
    }
}