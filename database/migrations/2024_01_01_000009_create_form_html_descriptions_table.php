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
        Schema::create('form_html_descriptions', function (Blueprint $table) {
            $table->id('description_id');
            $table->foreignId('form_id')->constrained('mediation_forms', 'form_id');
            $table->longText('html_content');
            $table->string('content_type')->default('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_html_descriptions');
    }
};

