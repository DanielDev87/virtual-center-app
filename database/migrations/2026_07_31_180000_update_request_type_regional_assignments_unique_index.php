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
        if (!Schema::hasTable('request_type_regional_assignments')) {
            return;
        }

        Schema::table('request_type_regional_assignments', function (Blueprint $table) {
            $table->unique(['request_type_id', 'institution_id', 'user_id'], 'rt_regional_user_unique');
            $table->dropUnique('rt_regional_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('request_type_regional_assignments')) {
            return;
        }

        Schema::table('request_type_regional_assignments', function (Blueprint $table) {
            $table->unique(['request_type_id', 'institution_id'], 'rt_regional_unique');
            $table->dropUnique('rt_regional_user_unique');
        });
    }
};
