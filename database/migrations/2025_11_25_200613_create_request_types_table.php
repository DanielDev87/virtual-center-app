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
        Schema::create('request_types', function (Blueprint $table) {
            $table->id('type_id');
            $table->string('type_name', 100);
            $table->text('type_description')->nullable();
            $table->string('type_icon', 50)->nullable(); // FontAwesome icon class
            $table->string('type_color', 7)->default('#6c757d'); // Hex color
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add request_type_id to tickets table
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('request_type_id')->nullable()->after('ticket_number');
            $table->foreign('request_type_id')->references('type_id')->on('request_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['request_type_id']);
            $table->dropColumn('request_type_id');
        });
        
        Schema::dropIfExists('request_types');
    }
};
