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
            $table->integer('resume_number')->nullable()->change();
            $table->string('requester_url', 255)->nullable()->change();
            $table->text('mediator_info')->nullable()->change();
            $table->text('check_points')->nullable()->change();
            $table->integer('total_points')->nullable()->change();
            $table->integer('priority')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('resume_number')->nullable(false)->change();
            $table->string('requester_url', 255)->nullable(false)->change();
            $table->text('mediator_info')->nullable(false)->change();
            $table->text('check_points')->nullable(false)->change();
            $table->integer('total_points')->nullable(false)->change();
            $table->integer('priority')->nullable(false)->change();
        });
    }
};
