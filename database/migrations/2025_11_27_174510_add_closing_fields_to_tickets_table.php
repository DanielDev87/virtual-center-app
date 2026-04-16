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
            if (!Schema::hasColumn('tickets', 'resource_link')) {
                $table->string('resource_link')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'is_reopened')) {
                $table->boolean('is_reopened')->default(false);
            }
            if (!Schema::hasColumn('tickets', 'reopened_at')) {
                $table->timestamp('reopened_at')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'rating')) {
                $table->integer('rating')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'feedback')) {
                $table->text('feedback')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['resource_link', 'is_reopened', 'reopened_at', 'rating', 'feedback']);
        });
    }
};
