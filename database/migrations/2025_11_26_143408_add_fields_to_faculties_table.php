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
        Schema::table('faculties', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('faculty_id')->constrained('institutions', 'institution_id')->onDelete('cascade');
            $table->text('faculty_description')->nullable()->after('faculty_name');
            $table->boolean('is_active')->default(true)->after('faculty_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id', 'faculty_description', 'is_active']);
        });
    }
};
