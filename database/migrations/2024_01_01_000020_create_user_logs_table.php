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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('contact_id')->nullable()->constrained('contacts', 'contact_id');
            $table->string('role')->nullable();
            $table->text('activity')->nullable();
            $table->string('hours')->nullable();
            $table->text('observation')->nullable();
            $table->text('url')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
