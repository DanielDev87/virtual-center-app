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
        Schema::create('project_tracking', function (Blueprint $table) {
            $table->id('tracking_id');
            $table->string('project_name');
            $table->text('project_description')->nullable();
            $table->foreignId('institution_id')->constrained('institutions', 'institution_id');
            $table->foreignId('material_type_id')->constrained('material_types', 'material_type_id');
            $table->enum('project_status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('project_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tracking');
    }
};

