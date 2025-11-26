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
        Schema::table('programs', function (Blueprint $table) {
            $table->string('program_code', 20)->nullable()->after('program_id');
            $table->text('program_description')->nullable()->after('program_name');
            $table->boolean('is_active')->default(true)->after('program_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['program_code', 'program_description', 'is_active']);
        });
    }
};
