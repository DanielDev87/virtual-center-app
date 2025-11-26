<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('request_type_id')->constrained('institutions', 'institution_id')->onDelete('set null');
            $table->foreignId('faculty_id')->nullable()->after('institution_id')->constrained('faculties', 'faculty_id')->onDelete('set null');
            $table->foreignId('program_id')->nullable()->after('faculty_id')->constrained('programs', 'program_id')->onDelete('set null');
            $table->foreignId('course_id')->nullable()->after('program_id')->constrained('courses', 'course_id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['program_id']);
            $table->dropForeign(['course_id']);
            $table->dropColumn(['institution_id', 'faculty_id', 'program_id', 'course_id']);
        });
    }
};
