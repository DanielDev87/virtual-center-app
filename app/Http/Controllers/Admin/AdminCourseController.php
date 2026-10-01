<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $hasPivotTable = Schema::hasTable('course_program');

        $query = Course::with($hasPivotTable ? ['program.faculty', 'programs.faculty'] : ['program.faculty']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('program_id')) {
            $programId = (int) $request->program_id;

            if ($hasPivotTable) {
                $query->where(function ($subQuery) use ($programId) {
                    $subQuery->where('program_id', $programId)
                        ->orWhereHas('programs', function ($programQuery) use ($programId) {
                            $programQuery->where('programs.program_id', $programId);
                        });
                });
            } else {
                $query->where('program_id', $programId);
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $courses = $query->orderBy('course_name')->paginate(15)->withQueryString();
        $programs = Program::where('is_active', true)->orderBy('program_name')->get();
        return view('admin.academic.courses.index', compact('courses', 'programs'));
    }

    public function create()
    {
        $programs = Program::where('is_active', true)->with('faculty')->get();
        return view('admin.academic.courses.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,program_id',
            'program_ids' => 'nullable|array',
            'program_ids.*' => 'integer|exists:programs,program_id',
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
        ]);

        $course = Course::create($request->all());
        $programIds = $request->input('program_ids', []);
        $programIds = array_values(array_unique(array_map('intval', array_filter($programIds))));

        if (empty($programIds)) {
            $programIds = [(int) $request->program_id];
        }

        $course->syncPrograms($programIds);

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'Curso creado exitosamente.');
    }

    public function edit($id)
    {
        $courseQuery = Course::query();

        if (Schema::hasTable('course_program')) {
            $courseQuery->with('programs');
        }

        $course = $courseQuery->findOrFail($id);
        $programs = Program::where('is_active', true)->with('faculty')->get();
        return view('admin.academic.courses.edit', compact('course', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,program_id',
            'program_ids' => 'nullable|array',
            'program_ids.*' => 'integer|exists:programs,program_id',
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $course = Course::findOrFail($id);
        $course->update($request->all());

        $programIds = $request->input('program_ids', []);
        $programIds = array_values(array_unique(array_map('intval', array_filter($programIds))));

        if (empty($programIds)) {
            $programIds = [(int) $request->program_id];
        }

        $course->syncPrograms($programIds);

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'Curso actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->update(['is_active' => false]);

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'Curso desactivado exitosamente.');
    }
}
