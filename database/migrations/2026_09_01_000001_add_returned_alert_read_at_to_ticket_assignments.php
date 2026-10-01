<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            $table->timestamp('returned_alert_read_at')->nullable()->after('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            $table->dropColumn('returned_alert_read_at');
        });
    }
};
