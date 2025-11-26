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
        Schema::table('users', function (Blueprint $table) {
            // Add profession field if it doesn't exist
            if (!Schema::hasColumn('users', 'user_profession')) {
                $table->string('user_profession', 100)->nullable()->after('user_bio');
            }
            
            // user_bio and user_avatar already exist in the table
            // Just making sure they are nullable
            $table->text('user_bio')->nullable()->change();
            $table->string('user_avatar', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_profession')) {
                $table->dropColumn('user_profession');
            }
        });
    }
};
