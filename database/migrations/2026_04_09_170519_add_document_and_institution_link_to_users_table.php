<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('document_number')->nullable()->unique()->after('user_name');
            $table->string('institution_link')->nullable()->after('document_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('document_number');
            $table->dropColumn('institution_link');
        });
    }
};
