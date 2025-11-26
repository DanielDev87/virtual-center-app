<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosition;

class JobPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'position_name' => 'Diseñador Gráfico',
                'position_description' => 'Responsable del diseño visual y gráfico de los recursos',
                'position_color' => '#FF6B6B',
                'is_active' => true,
            ],
            [
                'position_name' => 'Programador',
                'position_description' => 'Desarrollo de componentes interactivos y funcionalidades técnicas',
                'position_color' => '#4ECDC4',
                'is_active' => true,
            ],
            [
                'position_name' => 'Productor Audiovisual',
                'position_description' => 'Producción y edición de contenido multimedia',
                'position_color' => '#95E1D3',
                'is_active' => true,
            ],
            [
                'position_name' => 'Corrector de Estilo',
                'position_description' => 'Revisión y corrección de textos y contenidos',
                'position_color' => '#F38181',
                'is_active' => true,
            ],
            [
                'position_name' => 'Experto Temático',
                'position_description' => 'Asesoría en contenido especializado y validación académica',
                'position_color' => '#AA96DA',
                'is_active' => true,
            ],
            [
                'position_name' => 'Guionista',
                'position_description' => 'Creación de guiones y narrativas para contenidos',
                'position_color' => '#FCBAD3',
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            JobPosition::updateOrCreate(
                ['position_name' => $position['position_name']],
                $position
            );
        }
    }
}
