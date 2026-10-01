<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RequestType;

class TopicsAndDepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['topic' => 'Acceso Sistema Académico (Estudiantes)', 'department' => 'Registro académico', 'sla' => 48],
            ['topic' => 'Gestión de Correo Institucional', 'department' => 'Infraestructura Tecnológica - TIC', 'sla' => 48],
            ['topic' => 'Soporte Infraestructura Tecnológica - TIC', 'department' => 'Infraestructura Tecnológica - TIC', 'sla' => 48],
            ['topic' => 'Educación Virtual', 'department' => 'Departamento Educación Virtual y a Distancia', 'sla' => 48],
            ['topic' => 'Centro Regional Apartadó', 'department' => 'Centro Regional Apartadó', 'sla' => null],
            ['topic' => 'Gestión Intranet', 'department' => 'Web master', 'sla' => 48],
            ['topic' => 'Servicios Generales', 'department' => 'Servicios Generales', 'sla' => 48],
            ['topic' => 'Solicitud de Reportes - SUI', 'department' => 'Departamento SUI', 'sla' => 48],
            ['topic' => 'Aulas Virtuales - Cursos de TIC', 'department' => 'Cursos TIC', 'sla' => 48],
            ['topic' => 'Cursos Administración Distancia', 'department' => 'Admon de empresas DISTANCIA', 'sla' => 48],
            ['topic' => 'Aulas Virtuales - Cursos de AFI', 'department' => 'Cursos de AFI (aulas virtuales)', 'sla' => 48],
            ['topic' => 'Mantenimiento Planta Física y vigilancia', 'department' => 'Mantenimiento Planta Física y vigilancia', 'sla' => null],
            ['topic' => 'Gestión Sistema de Control de Acceso', 'department' => 'Infraestructura Tecnológica - TIC', 'sla' => 48],
            ['topic' => 'Soporte SUI - SW para la Operación', 'department' => 'Departamento SUI', 'sla' => 48],
            ['topic' => 'Gestión activos fijos', 'department' => null, 'sla' => null],
            ['topic' => 'Acceso a Turnitin - Docentes', 'department' => 'Infraestructura Tecnológica - TIC', 'sla' => 48],
            ['topic' => 'Solicitudes Habeas Data', 'department' => 'Área de Habeas Data', 'sla' => null],
            ['topic' => 'Solicitud de Información - Dirección de Planeación', 'department' => 'Dirección de planeación', 'sla' => null],
            ['topic' => 'Acceso Sistema Académico (Docentes y Administrativos)', 'department' => 'Gestión Humana', 'sla' => null],
            ['topic' => 'Soporte salas de sistemas y medios digitales', 'department' => 'Infraestructura Tecnológica - TIC', 'sla' => 48],
        ];

        foreach ($topics as $data) {
            $departmentId = null;
            if ($data['department']) {
                $department = DB::table('departments')->where('department_name', $data['department'])->first();
                if (!$department) {
                    $departmentId = DB::table('departments')->insertGetId([
                        'department_name' => $data['department'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $departmentId = $department->department_id;
                }
            }

            // Update or create request_type
            $type = RequestType::where('type_name', $data['topic'])->first();
            if ($type) {
                $type->update([
                    'department_id' => $departmentId,
                    'sla_hours' => $data['sla']
                ]);
            } else {
                RequestType::create([
                    'type_name' => $data['topic'],
                    'type_description' => 'Solicitud relacionada con ' . $data['topic'],
                    'type_icon' => 'fa-ticket-alt',
                    'type_color' => '#0280AE',
                    'is_active' => true,
                    'department_id' => $departmentId,
                    'sla_hours' => $data['sla']
                ]);
            }
        }
    }
}
