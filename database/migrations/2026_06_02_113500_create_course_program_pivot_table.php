<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_program', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained('courses', 'course_id')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs', 'program_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['course_id', 'program_id']);
        });

        $now = now();

        DB::table('courses')
            ->select('course_id', 'program_id')
            ->whereNotNull('program_id')
            ->orderBy('course_id')
            ->chunk(500, function ($courses) use ($now) {
                $rows = [];

                foreach ($courses as $course) {
                    $rows[] = [
                        'course_id' => (int) $course->course_id,
                        'program_id' => (int) $course->program_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($rows)) {
                    DB::table('course_program')->upsert(
                        $rows,
                        ['course_id', 'program_id'],
                        ['updated_at']
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_program');
    }
};
