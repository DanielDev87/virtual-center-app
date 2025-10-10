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
        Schema::create('mediation_forms', function (Blueprint $table) {
            $table->id('form_id');
            $table->string('form_title');
            $table->text('form_description')->nullable();
            $table->json('form_data')->nullable();
            $table->foreignId('created_by')->constrained('users', 'user_id');
            $table->enum('form_status', ['draft', 'active', 'archived'])->default('draft');
            $table->date('form_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediation_forms');
    }
};

