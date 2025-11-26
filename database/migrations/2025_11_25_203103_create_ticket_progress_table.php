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
        Schema::create('ticket_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id'); // Contributor who made the update
            $table->text('progress_description');
            $table->integer('progress_percentage')->default(0); // 0-100
            $table->string('status_update', 50)->nullable(); // Optional status change
            $table->timestamps();

            $table->foreign('ticket_id')->references('ticket_id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_progress');
    }
};
