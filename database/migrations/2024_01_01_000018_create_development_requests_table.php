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
        Schema::create('development_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id');
            $table->string('development_type');
            $table->date('delivery_date')->nullable();
            $table->text('files_url')->nullable();
            $table->text('product_url')->nullable();
            $table->text('info');
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
        Schema::dropIfExists('development_requests');
    }
};
