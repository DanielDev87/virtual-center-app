<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\Program;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBulkImportTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole(string $roleName, string $emailPrefix): User
    {
        $role = UserRole::firstOrCreate(
            ['role_name' => $roleName],
            ['role_description' => $roleName . ' role', 'is_active' => true]
        );

        return User::create([
            'user_name' => $roleName . ' User',
            'user_email' => $emailPrefix . '_' . time() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_bulk_import_roles_from_csv()
    {
        $admin = $this->createUserWithRole('Admin', 'bulk_admin');

        $csv = implode("\n", [
            'role_name,role_description,role_color,is_active',
            'Analista de Mesa,Rol creado por importacion,#112233,true',
        ]);

        $file = UploadedFile::fake()->createWithContent('roles.csv', $csv);

        $response = $this->actingAs($admin)
            ->post(route('admin.bulk-import.store'), [
                'entity' => 'roles',
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.bulk-import.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_roles', [
            'role_name' => 'Analista de Mesa',
            'role_color' => '#112233',
            'is_active' => 1,
        ]);
    }

    /** @test */
    public function monitor_cannot_open_bulk_import_screen()
    {
        $monitor = $this->createUserWithRole('Monitor', 'bulk_monitor');

        $response = $this->actingAs($monitor)
            ->get(route('admin.bulk-import.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_preview_import_without_persisting_data()
    {
        $admin = $this->createUserWithRole('Admin', 'bulk_admin_preview');

        $csv = implode("\n", [
            'role_name,role_description,role_color,is_active',
            'Rol Solo Preview,No debe guardarse,#334455,true',
        ]);

        $file = UploadedFile::fake()->createWithContent('roles-preview.csv', $csv);

        $response = $this->actingAs($admin)
            ->post(route('admin.bulk-import.store'), [
                'entity' => 'roles',
                'action' => 'preview',
                'file' => $file,
            ]);

        $response->assertRedirect(route('admin.bulk-import.index'));
        $response->assertSessionHas('import_preview');

        $this->assertDatabaseMissing('user_roles', [
            'role_name' => 'Rol Solo Preview',
        ]);
    }

    /** @test */
    public function admin_can_download_csv_template_for_entities()
    {
        $admin = $this->createUserWithRole('Admin', 'bulk_admin_template');

        $response = $this->actingAs($admin)
            ->get(route('admin.bulk-import.template', ['entity' => 'roles']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload('plantilla_roles.csv');
    }

    /** @test */
    public function admin_can_bulk_import_job_positions_and_academic_entities()
    {
        $admin = $this->createUserWithRole('Admin', 'bulk_admin_catalog');

        $institution = Institution::create([
            'institution_name' => 'Institucion Base',
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'faculty_name' => 'Facultad Base',
            'institution_id' => $institution->institution_id,
            'is_active' => true,
        ]);

        $program = Program::create([
            'program_name' => 'Programa Base',
            'program_code' => 'PRG-BASE',
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        $jobPositionsCsv = implode("\n", [
            'position_name,position_description,position_color,is_active',
            'Analista N1,Soporte de primer nivel,#123456,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'job_positions',
            'file' => UploadedFile::fake()->createWithContent('job_positions.csv', $jobPositionsCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('job_positions', [
            'position_name' => 'Analista N1',
            'position_color' => '#123456',
        ]);

        $institutionsCsv = implode("\n", [
            'institution_name,institution_description,is_active',
            'Institucion CSV,Institucion creada por carga,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'institutions',
            'file' => UploadedFile::fake()->createWithContent('institutions.csv', $institutionsCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('institutions', [
            'institution_name' => 'Institucion CSV',
        ]);

        $facultiesCsv = implode("\n", [
            'faculty_name,faculty_description,institution_name,is_active',
            'Facultad CSV,Facultad importada,Institucion Base,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'faculties',
            'file' => UploadedFile::fake()->createWithContent('faculties.csv', $facultiesCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('faculties', [
            'faculty_name' => 'Facultad CSV',
            'institution_id' => $institution->institution_id,
        ]);

        $areasCsv = implode("\n", [
            'area_name,area_description,faculty_name,institution_name,is_active',
            'Area CSV,Area importada,Facultad Base,Institucion Base,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'areas',
            'file' => UploadedFile::fake()->createWithContent('areas.csv', $areasCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('areas', [
            'area_name' => 'Area CSV',
            'faculty_id' => $faculty->faculty_id,
        ]);

        $programsCsv = implode("\n", [
            'program_code,program_name,program_description,faculty_name,institution_name,is_active',
            'PRG-CSV,Programa CSV,Programa importado,Facultad Base,Institucion Base,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'programs',
            'file' => UploadedFile::fake()->createWithContent('programs.csv', $programsCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('programs', [
            'program_name' => 'Programa CSV',
            'program_code' => 'PRG-CSV',
            'faculty_id' => $faculty->faculty_id,
        ]);

        $coursesCsv = implode("\n", [
            'course_code,course_name,course_description,credits,program_code,is_active',
            'CUR-CSV,Curso CSV,Curso importado,4,PRG-BASE,true',
        ]);

        $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'courses',
            'file' => UploadedFile::fake()->createWithContent('courses.csv', $coursesCsv),
        ])->assertRedirect(route('admin.bulk-import.index'));

        $this->assertDatabaseHas('courses', [
            'course_code' => 'CUR-CSV',
            'course_name' => 'Curso CSV',
            'program_id' => $program->program_id,
            'credits' => 4,
        ]);
    }

    /** @test */
    public function admin_can_bulk_import_course_with_multiple_program_codes()
    {
        $admin = $this->createUserWithRole('Admin', 'bulk_admin_multi_program');

        $institution = Institution::create([
            'institution_name' => 'Institucion Multi Program',
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'faculty_name' => 'Facultad Multi Program',
            'institution_id' => $institution->institution_id,
            'is_active' => true,
        ]);

        $programA = Program::create([
            'program_name' => 'Programa A Multi',
            'program_code' => 'PRG-A-MULTI',
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        $programB = Program::create([
            'program_name' => 'Programa B Multi',
            'program_code' => 'PRG-B-MULTI',
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        $coursesCsv = implode("\n", [
            'course_code,course_name,course_description,credits,program_codes,is_active',
            'CUR-MULTI,Curso Multi Import,Curso importado en multiples programas,4,PRG-A-MULTI|PRG-B-MULTI,true',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bulk-import.store'), [
            'entity' => 'courses',
            'file' => UploadedFile::fake()->createWithContent('courses-multi.csv', $coursesCsv),
        ]);

        $response->assertRedirect(route('admin.bulk-import.index'));
        $response->assertSessionHas('success');

        $course = Course::where('course_code', 'CUR-MULTI')->firstOrFail();

        $pivotProgramIds = DB::table('course_program')
            ->where('course_id', $course->course_id)
            ->pluck('program_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $expected = [(int) $programA->program_id, (int) $programB->program_id];
        sort($expected);

        $this->assertSame($expected, $pivotProgramIds);
        $this->assertSame((int) $programA->program_id, (int) $course->program_id);
    }
}
