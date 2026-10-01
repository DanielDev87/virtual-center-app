<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_join_requests', function (Blueprint $table) {
            $table->id('join_request_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('request_note')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['ticket_id', 'requester_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_join_requests');
    }
};
