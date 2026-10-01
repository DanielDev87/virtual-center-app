<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure faculty_name can store long names in production databases
     * that may still have legacy smaller varchar sizes.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE faculties MODIFY faculty_name VARCHAR(255) NOT NULL");
    }

    /**
     * Keep rollback non-destructive to avoid accidental truncation.
     */
    public function down(): void
    {
        // Intentionally left without shrinking the column.
    }
};
