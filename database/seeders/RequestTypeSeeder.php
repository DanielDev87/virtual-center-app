<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;

class RequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'type_name' => 'Curso Virtual',
                'type_description' => 'Solicitud para creación de curso virtual en plataforma educativa',
                'type_icon' => 'fa-graduation-cap',
                'type_color' => '#0d6efd',
                'is_active' => true,
            ],
            [
                'type_name' => 'Video',
                'type_description' => 'Solicitud para producción de video educativo o institucional',
                'type_icon' => 'fa-video',
                'type_color' => '#dc3545',
                'is_active' => true,
            ],
            [
                'type_name' => 'Podcast',
                'type_description' => 'Solicitud para producción de podcast o audio',
                'type_icon' => 'fa-podcast',
                'type_color' => '#6f42c1',
                'is_active' => true,
            ],
            [
                'type_name' => 'Diseño Gráfico',
                'type_description' => 'Solicitud para diseño de material gráfico (banners, flyers, etc.)',
                'type_icon' => 'fa-palette',
                'type_color' => '#fd7e14',
                'is_active' => true,
            ],
            [
                'type_name' => 'Desarrollo Web',
                'type_description' => 'Solicitud para desarrollo o actualización de sitio web',
                'type_icon' => 'fa-code',
                'type_color' => '#20c997',
                'is_active' => true,
            ],
            [
                'type_name' => 'Soporte Técnico',
                'type_description' => 'Solicitud de soporte técnico para plataformas o herramientas',
                'type_icon' => 'fa-tools',
                'type_color' => '#6c757d',
                'is_active' => true,
            ],
            [
                'type_name' => 'Otro',
                'type_description' => 'Otro tipo de solicitud no categorizada',
                'type_icon' => 'fa-question-circle',
                'type_color' => '#adb5bd',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            RequestType::updateOrCreate(
                ['type_name' => $type['type_name']],
                $type
            );
        }

        $this->command->info('✅ Tipos de solicitud creados exitosamente!');
    }
}
