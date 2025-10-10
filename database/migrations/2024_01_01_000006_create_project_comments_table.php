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
        Schema::create('project_comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->foreignId('tracking_id')->constrained('project_tracking', 'tracking_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->text('comment_content');
            $table->enum('comment_type', ['general', 'feedback', 'issue', 'update'])->default('general');
            $table->boolean('is_important')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};

