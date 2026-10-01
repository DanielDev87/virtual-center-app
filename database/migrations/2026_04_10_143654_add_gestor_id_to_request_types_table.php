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
        Schema::table('request_types', function (Blueprint $table) {
            if (!Schema::hasColumn('request_types', 'gestor_id')) {
                $table->unsignedBigInteger('gestor_id')->nullable()->after('type_description');
                $table->foreign('gestor_id')->references('user_id')->on('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_types', function (Blueprint $table) {
            if (Schema::hasColumn('request_types', 'gestor_id')) {
                $table->dropForeign(['gestor_id']);
                $table->dropColumn('gestor_id');
            }
        });
    }
};
