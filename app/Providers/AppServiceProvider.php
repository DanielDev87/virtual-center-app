<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\RequestType;
use App\Models\Ticket;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    private const BRANDING_STORAGE_PREFIXES = [
        'storage/branding/',
        'branding/',
    ];

    /**
     * Registrar los servicios de la aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Iniciar los servicios de la aplicación.
     */
    public function boot(): void
    {
        // 1. Paginación con Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. Configuración de base de datos
        Schema::defaultStringLength(191);
        
        // 2. Configuración de zona horaria
        date_default_timezone_set('America/Bogota');

        // 3. Forzar HTTPS si estamos usando ngrok
        if (str_contains(config('app.url'), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }

        $branding = [
            'app_name' => 'Sistema de Soporte Universitario',
            'short_name' => 'Soporte Univ.',
            'logo_path' => 'img/logomsula.png',
            'footer_logo_path' => 'img/LogoCampus.png',
            'favicon_path' => 'img/logomsula.png',
            'primary_color' => '#0280AE',
            'secondary_color' => '#17a2b8',
        ];

        $mailFrom = [
            'address' => (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
            'name' => (string) config('mail.from.name', env('MAIL_FROM_NAME', config('app.name', 'Laravel'))),
        ];

        $mailSmtp = [
            'host' => (string) config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.mailgun.org')),
            'port' => (int) config('mail.mailers.smtp.port', env('MAIL_PORT', 587)),
            'encryption' => config('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'tls')),
        ];

        try {
            if (Schema::hasTable('app_settings')) {
                $stored = AppSetting::query()
                    ->whereIn('setting_key', [
                        'branding_app_name',
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
                    ])
                    ->pluck('setting_value', 'setting_key');

                $branding['app_name'] = $stored->get('branding_app_name', $branding['app_name']);
                $branding['short_name'] = $stored->get('branding_short_name', $branding['short_name']);
                $branding['logo_path'] = ltrim((string) $stored->get('branding_logo_path', $branding['logo_path']), '/');
                $branding['footer_logo_path'] = ltrim((string) $stored->get('branding_footer_logo_path', $branding['footer_logo_path']), '/');
                $branding['favicon_path'] = ltrim((string) $stored->get('branding_favicon_path', $branding['favicon_path']), '/');
                $branding['primary_color'] = $stored->get('branding_primary_color', $branding['primary_color']);
                $branding['secondary_color'] = $stored->get('branding_secondary_color', $branding['secondary_color']);
                $mailFrom['name'] = (string) $stored->get('mail_from_name', $mailFrom['name']);
                $mailFrom['address'] = (string) $stored->get('mail_from_address', $mailFrom['address']);
                $mailSmtp['host'] = (string) $stored->get('mail_smtp_host', $mailSmtp['host']);
                $mailSmtp['port'] = (int) $stored->get('mail_smtp_port', $mailSmtp['port']);
                $mailSmtpEncryption = (string) $stored->get('mail_smtp_encryption', (string) $mailSmtp['encryption']);
                $mailSmtp['encryption'] = $mailSmtpEncryption !== '' ? $mailSmtpEncryption : null;
            }
        } catch (\Throwable $e) {
            // Keep defaults when app_settings is not available yet.
        }

        config([
            'mail.from.name' => $mailFrom['name'],
            'mail.from.address' => $mailFrom['address'],
            'mail.mailers.smtp.host' => $mailSmtp['host'],
            'mail.mailers.smtp.port' => $mailSmtp['port'],
            'mail.mailers.smtp.encryption' => $mailSmtp['encryption'],
        ]);

        $branding['logo_url'] = $this->resolveBrandingAssetUrl($branding['logo_path']);
        $branding['footer_logo_url'] = $this->resolveBrandingAssetUrl($branding['footer_logo_path']);
        $branding['favicon_url'] = $this->resolveBrandingAssetUrl($branding['favicon_path']);
        $branding['favicon_mime'] = $this->detectAssetMimeType($branding['favicon_path']);

        View::share('branding', $branding);

        View::composer('layouts.contributor', function ($view) {
            $topicQueueCount = 0;

            try {
                $user = Auth::user();

                if ($user && optional($user->role)->role_name === 'Contributor') {
                    $userId = (int) $user->user_id;

                    if (Schema::hasTable('request_type_user')) {
                        $topicIds = RequestType::where(function ($query) use ($user) {
                                $query->whereHas('collaborators', function ($collaboratorQuery) use ($user) {
                                    $collaboratorQuery->where('users.user_id', $user->user_id);
                                })->orWhere('gestor_id', $user->user_id);
                            })
                            ->pluck('type_id');
                    } else {
                        $topicIds = RequestType::where('gestor_id', $user->user_id)
                            ->pluck('type_id');
                    }

                    if (Schema::hasTable('request_type_regional_assignments')) {
                        $regionalTopicIds = DB::table('request_type_regional_assignments')
                            ->where('user_id', $userId)
                            ->pluck('request_type_id');

                        $topicIds = $topicIds
                            ->merge($regionalTopicIds)
                            ->unique()
                            ->values();
                    }

                    if ($topicIds->isNotEmpty()) {
                        $topicQueueCount = Ticket::whereIn('request_type_id', $topicIds)
                            ->whereNotIn('status', [3, 4])
                            ->where(function ($scope) use ($userId) {
                                if (!Schema::hasTable('request_type_regional_assignments')) {
                                    $scope->whereRaw('1 = 1');
                                    return;
                                }

                                $scope->whereNotExists(function ($subQuery) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('request_type_regional_assignments as regional_any')
                                        ->whereColumn('regional_any.request_type_id', 'tickets.request_type_id');
                                })->orWhereExists(function ($subQuery) use ($userId) {
                                    $subQuery->select(DB::raw(1))
                                        ->from('request_type_regional_assignments as regional_user')
                                        ->whereColumn('regional_user.request_type_id', 'tickets.request_type_id')
                                        ->whereColumn('regional_user.institution_id', 'tickets.institution_id')
                                        ->where('regional_user.user_id', $userId);
                                });
                            })
                            ->count();
                    }
                }
            } catch (\Throwable $e) {
                $topicQueueCount = 0;
            }

            $view->with('topicQueueCount', $topicQueueCount);
        });
    }

    private function resolveBrandingAssetUrl(string $path): string
    {
        $normalizedPath = ltrim($path, '/');

        foreach (self::BRANDING_STORAGE_PREFIXES as $prefix) {
            if (str_starts_with($normalizedPath, $prefix)) {
                return url('/branding-assets/'.rawurlencode(basename($normalizedPath)));
            }
        }

        return asset($normalizedPath);
    }

    private function detectAssetMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
    }
}