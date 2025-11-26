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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->bigInteger('ticket_number')->unique();
            $table->string('title');
            $table->integer('type');
            $table->integer('resume_number');
            $table->integer('status');
            $table->foreignId('requester_id')->constrained('users', 'user_id');
            $table->text('requester_url')->nullable();
            $table->longText('requester_info')->nullable();
            $table->foreignId('mediator_id')->nullable()->constrained('users', 'user_id');
            $table->longText('mediator_info')->nullable();
            $table->integer('check_points')->nullable();
            $table->integer('total_points')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('priority')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
