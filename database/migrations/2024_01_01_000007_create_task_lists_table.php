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
        Schema::create('task_lists', function (Blueprint $table) {
            $table->id('task_id');
            $table->string('task_name');
            $table->text('task_description')->nullable();
            $table->foreignId('assigned_to')->constrained('users', 'user_id');
            $table->foreignId('assigned_by')->constrained('users', 'user_id');
            $table->date('assignment_date');
            $table->date('due_date')->nullable();
            $table->enum('task_status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('task_notes')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_lists');
    }
};


