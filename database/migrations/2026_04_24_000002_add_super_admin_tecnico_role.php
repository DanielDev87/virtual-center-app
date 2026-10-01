<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('user_roles')->updateOrInsert(
            ['role_name' => 'Super Admin Tecnico'],
            [
                'role_description' => 'Gestiona configuraciones tecnicas del sistema',
                'role_color' => '#0a58ca',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_roles')->where('role_name', 'Super Admin Tecnico')->delete();
    }
};
