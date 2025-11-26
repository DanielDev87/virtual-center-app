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
        Schema::create('multimedia_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id');
            $table->integer('request_type');
            $table->date('delivery_date')->nullable();
            $table->text('script_url');
            $table->longText('observation')->nullable();
            $table->boolean('camera')->default(false);
            $table->boolean('microphone')->default(false);
            $table->boolean('lights')->default(false);
            $table->text('sharepoint_url')->nullable();
            $table->text('other_url')->nullable();
            $table->integer('subarea_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multimedia_requests');
    }
};
