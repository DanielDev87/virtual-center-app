<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;

class InstitutionalTopicsSeeder extends Seeder
{
    /**
     * Tópicos institucionales con iconos Font Awesome y SLA.
     * Fuente: manuales/iconos-fa-para topicos.md
     */
    public function run(): void
    {
        $topics = [
            [
                'type_name'        => 'Acceso Sistema Académico (Estudiantes)',
                'type_description' => 'Soporte y gestión de acceso al sistema académico para estudiantes.',
                'type_icon'        => 'fa-user-graduate',
                'type_color'       => '#0d6efd',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Gestión de Correo Institucional',
                'type_description' => 'Creación, recuperación y soporte de cuentas de correo institucional.',
                'type_icon'        => 'fa-envelope-open',
                'type_color'       => '#0dcaf0',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Soporte Infraestructura Tecnológica - TIC',
                'type_description' => 'Soporte técnico de servidores, redes y equipos de infraestructura TIC.',
                'type_icon'        => 'fa-server',
                'type_color'       => '#6c757d',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Educación Virtual',
                'type_description' => 'Gestión de plataformas y recursos del Departamento de Educación Virtual y a Distancia.',
                'type_icon'        => 'fa-chalkboard-teacher',
                'type_color'       => '#198754',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Centro Regional Apartadó',
                'type_description' => 'Solicitudes y soporte del Centro Regional de Apartadó.',
                'type_icon'        => 'fa-map-marker-alt',
                'type_color'       => '#fd7e14',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Gestión Intranet',
                'type_description' => 'Soporte y gestión del portal Intranet institucional.',
                'type_icon'        => 'fa-globe',
                'type_color'       => '#0d6efd',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Servicios Generales',
                'type_description' => 'Solicitudes de servicios generales institucionales.',
                'type_icon'        => 'fa-concierge-bell',
                'type_color'       => '#adb5bd',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Solicitud de Reportes - SUI',
                'type_description' => 'Solicitud y generación de reportes del sistema SUI.',
                'type_icon'        => 'fa-chart-bar',
                'type_color'       => '#6f42c1',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Aulas Virtuales - Cursos de TIC',
                'type_description' => 'Creación y soporte de aulas virtuales para cursos de TIC.',
                'type_icon'        => 'fa-desktop',
                'type_color'       => '#20c997',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Cursos Administración Distancia',
                'type_description' => 'Gestión de cursos de Administración de Empresas en modalidad distancia.',
                'type_icon'        => 'fa-briefcase',
                'type_color'       => '#795548',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Aulas Virtuales - Cursos de AFI',
                'type_description' => 'Creación y soporte de aulas virtuales para cursos de AFI.',
                'type_icon'        => 'fa-video',
                'type_color'       => '#dc3545',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Mantenimiento Planta Física y Vigilancia',
                'type_description' => 'Solicitudes de mantenimiento de planta física y servicios de vigilancia.',
                'type_icon'        => 'fa-hard-hat',
                'type_color'       => '#fd7e14',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Gestión Sistema de Control de Acceso',
                'type_description' => 'Administración y soporte del sistema de control de acceso físico y lógico.',
                'type_icon'        => 'fa-shield-alt',
                'type_color'       => '#0d6efd',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Soporte SUI - SW para la Operación',
                'type_description' => 'Soporte del software SUI utilizado en la operación institucional.',
                'type_icon'        => 'fa-cogs',
                'type_color'       => '#6c757d',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Gestión Activos Fijos',
                'type_description' => 'Registro, control y solicitudes relacionadas con activos fijos institucionales.',
                'type_icon'        => 'fa-boxes',
                'type_color'       => '#795548',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Acceso a Turnitin - Docentes',
                'type_description' => 'Solicitud de acceso y soporte a la plataforma Turnitin para docentes.',
                'type_icon'        => 'fa-file-contract',
                'type_color'       => '#198754',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Solicitudes Habeas Data',
                'type_description' => 'Solicitudes relacionadas con el derecho de Habeas Data y protección de datos personales.',
                'type_icon'        => 'fa-user-shield',
                'type_color'       => '#6f42c1',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Solicitud de Información - Dirección de Planeación',
                'type_description' => 'Solicitudes de información institucional dirigidas a la Dirección de Planeación.',
                'type_icon'        => 'fa-clipboard-list',
                'type_color'       => '#0dcaf0',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Acceso Sistema Académico (Docentes y Administrativos)',
                'type_description' => 'Soporte y gestión de acceso al sistema académico para docentes y personal administrativo.',
                'type_icon'        => 'fa-id-card',
                'type_color'       => '#0d6efd',
                'sla_hours'        => null,
                'is_active'        => true,
            ],
            [
                'type_name'        => 'Soporte Salas de Sistemas y Medios Digitales',
                'type_description' => 'Soporte técnico de salas de sistemas, laboratorios y medios digitales.',
                'type_icon'        => 'fa-laptop',
                'type_color'       => '#20c997',
                'sla_hours'        => 48,
                'is_active'        => true,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($topics as $topic) {
            $existing = RequestType::where('type_name', $topic['type_name'])->first();

            if ($existing) {
                $existing->update($topic);
                $updated++;
            } else {
                RequestType::create($topic);
                $created++;
            }
        }

        $this->command->info("Tópicos institucionales procesados: {$created} creados, {$updated} actualizados.");
    }
}
