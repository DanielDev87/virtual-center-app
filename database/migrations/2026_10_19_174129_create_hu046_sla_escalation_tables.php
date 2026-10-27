<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sla_escalation_rules')) {
            Schema::create('sla_escalation_rules', function (Blueprint $table) {
                $table->id('rule_id');
                $table->unsignedBigInteger('request_type_id')->index();
                $table->unsignedTinyInteger('priority')->nullable();
                $table->unsignedTinyInteger('threshold_percent')->default(25);
                $table->unsignedBigInteger('escalate_user_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sla_escalations')) {
            Schema::create('sla_escalations', function (Blueprint $table) {
                $table->id('escalation_id');
                $table->unsignedBigInteger('ticket_id')->index();
                $table->unsignedBigInteger('rule_id')->nullable();
                $table->unsignedTinyInteger('threshold_percent');
                $table->integer('remaining_minutes')->default(0);
                $table->unsignedInteger('recipients_count')->default(0);
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();
                $table->unique(['ticket_id', 'threshold_percent'], 'sla_escalations_ticket_threshold_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_escalations');
        Schema::dropIfExists('sla_escalation_rules');
    }
};