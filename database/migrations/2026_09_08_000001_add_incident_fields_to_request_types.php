<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_types', function (Blueprint $table) {
            $table->boolean('incident_active')->default(false)->after('is_active');
            $table->string('incident_title')->nullable()->after('incident_active');
            $table->text('incident_message')->nullable()->after('incident_title');
            $table->timestamp('incident_started_at')->nullable()->after('incident_message');
        });
    }

    public function down(): void
    {
        Schema::table('request_types', function (Blueprint $table) {
            $table->dropColumn(['incident_active', 'incident_title', 'incident_message', 'incident_started_at']);
        });
    }
};
