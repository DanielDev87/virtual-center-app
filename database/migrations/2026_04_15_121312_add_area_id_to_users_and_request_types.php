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
        Schema::table('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas', 'area_id')->onDelete('set null');
        });

        Schema::table('request_types', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas', 'area_id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_types', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_id')->nullable(false)->change();
        });
    }
};
