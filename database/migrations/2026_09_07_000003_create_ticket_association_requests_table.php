<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_association_requests')) {
            Schema::table('ticket_association_requests', function (Blueprint $table) {
                $table->index(['parent_ticket_id', 'child_ticket_id', 'status'], 'ticket_assoc_status_idx');
            });
            return;
        }

        Schema::create('ticket_association_requests', function (Blueprint $table) {
            $table->id('association_request_id');
            $table->foreignId('parent_ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('child_ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('request_note')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['parent_ticket_id', 'child_ticket_id', 'status'], 'ticket_assoc_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_association_requests');
    }
};
