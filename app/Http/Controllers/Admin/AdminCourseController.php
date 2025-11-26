<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('program.faculty')->latest()->paginate(15);
        return view('admin.academic.courses.index', compact('courses'));
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
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
        ]);

        Course::create($request->all());

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'Curso creado exitosamente.');
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $programs = Program::where('is_active', true)->with('faculty')->get();
        return view('admin.academic.courses.edit', compact('course', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,program_id',
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:255',
            'course_description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);

        $course = Course::findOrFail($id);
        $course->update($request->all());

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
