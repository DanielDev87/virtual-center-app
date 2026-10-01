<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\Program;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCourseManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function createAdmin(): User
    {
        $role = UserRole::firstOrCreate(
            ['role_name' => 'Admin'],
            ['role_description' => 'Admin role', 'is_active' => true]
        );

        return User::create([
            'user_name' => 'Admin Courses Test',
            'user_email' => 'admin_courses_' . uniqid() . '@test.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id' => $role->role_id,
            'is_active' => true,
        ]);
    }

    private function createAcademicBase(): array
    {
        $institution = Institution::create([
            'institution_name' => 'Institucion Test ' . uniqid(),
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'faculty_name' => 'Facultad Test ' . uniqid(),
            'institution_id' => $institution->institution_id,
            'is_active' => true,
        ]);

        $programA = Program::create([
            'program_name' => 'Programa A ' . uniqid(),
            'program_code' => 'PRA' . rand(1000, 9999),
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        $programB = Program::create([
            'program_name' => 'Programa B ' . uniqid(),
            'program_code' => 'PRB' . rand(1000, 9999),
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        $programC = Program::create([
            'program_name' => 'Programa C ' . uniqid(),
            'program_code' => 'PRC' . rand(1000, 9999),
            'faculty_id' => $faculty->faculty_id,
            'is_active' => true,
        ]);

        return [$institution, $faculty, $programA, $programB, $programC];
    }

    /** @test */
    public function admin_can_store_course_with_multiple_program_ids_and_sync_pivot()
    {
        $admin = $this->createAdmin();
        [, , $programA, $programB] = $this->createAcademicBase();

        $response = $this->actingAs($admin)->post(route('admin.academic.courses.store'), [
            'program_id' => $programA->program_id,
            'program_ids' => [$programB->program_id, $programA->program_id, $programB->program_id],
            'course_code' => 'CUR-MULTI-' . rand(1000, 9999),
            'course_name' => 'Curso Multi Programa',
            'course_description' => 'Prueba de relacion multiple',
            'credits' => 3,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.courses.index'));
        $response->assertSessionHas('success');

        $course = Course::where('course_name', 'Curso Multi Programa')->firstOrFail();

        // The first value in program_ids is used as legacy program_id.
        $this->assertSame((int) $programB->program_id, (int) $course->program_id);

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
    }

    /** @test */
    public function update_without_program_ids_falls_back_to_program_id_and_resyncs_pivot()
    {
        $admin = $this->createAdmin();
        [, , $programA, $programB, $programC] = $this->createAcademicBase();

        $course = Course::create([
            'program_id' => $programA->program_id,
            'course_code' => 'CUR-UPD-' . rand(1000, 9999),
            'course_name' => 'Curso Para Actualizar',
            'course_description' => 'Inicial',
            'credits' => 2,
            'is_active' => true,
        ]);

        $course->syncPrograms([$programA->program_id, $programB->program_id]);

        $response = $this->actingAs($admin)->put(route('admin.academic.courses.update', $course->course_id), [
            'program_id' => $programC->program_id,
            'course_code' => $course->course_code,
            'course_name' => 'Curso Actualizado',
            'course_description' => 'Actualizado',
            'credits' => 4,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.academic.courses.index'));
        $response->assertSessionHas('success');

        $course->refresh();
        $this->assertSame((int) $programC->program_id, (int) $course->program_id);
        $this->assertSame('Curso Actualizado', $course->course_name);

        $pivotProgramIds = DB::table('course_program')
            ->where('course_id', $course->course_id)
            ->pluck('program_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->assertSame([(int) $programC->program_id], $pivotProgramIds);
    }

    /** @test */
    public function index_filter_by_program_id_includes_courses_linked_through_pivot()
    {
        $admin = $this->createAdmin();
        [, , $programA, $programB] = $this->createAcademicBase();

        $visibleCourse = Course::create([
            'program_id' => $programA->program_id,
            'course_code' => 'CUR-VIS-' . rand(1000, 9999),
            'course_name' => 'Curso Visible Filtro',
            'credits' => 3,
            'is_active' => true,
        ]);
        $visibleCourse->syncPrograms([$programA->program_id, $programB->program_id]);

        $hiddenCourse = Course::create([
            'program_id' => $programA->program_id,
            'course_code' => 'CUR-HID-' . rand(1000, 9999),
            'course_name' => 'Curso No Visible Filtro',
            'credits' => 3,
            'is_active' => true,
        ]);
        $hiddenCourse->syncPrograms([$programA->program_id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.academic.courses.index', ['program_id' => $programB->program_id]));

        $response->assertOk();
        $response->assertSee('Curso Visible Filtro');
        $response->assertDontSee('Curso No Visible Filtro');
    }
}
