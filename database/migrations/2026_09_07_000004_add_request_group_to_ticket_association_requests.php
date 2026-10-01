<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_association_requests', function (Blueprint $table) {
            $table->uuid('request_group')->nullable()->after('requested_by');
            $table->index('request_group', 'ticket_assoc_group_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_association_requests', function (Blueprint $table) {
            $table->dropIndex('ticket_assoc_group_idx');
            $table->dropColumn('request_group');
        });
    }
};
