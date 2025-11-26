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
        Schema::create('station_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id');
            $table->text('request_type');
            $table->date('delivery_date')->nullable();
            $table->text('instructions_url');
            $table->text('observation');
            $table->text('product')->nullable();
            $table->integer('subarea_id');
            $table->date('request_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_requests');
    }
};
