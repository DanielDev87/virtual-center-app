<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('parent_ticket_id')
                ->nullable()
                ->after('ticket_id')
                ->constrained('tickets', 'ticket_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['parent_ticket_id']);
            $table->dropColumn('parent_ticket_id');
        });
    }
};
