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
        Schema::create('request_type_regional_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_type_id');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['request_type_id', 'institution_id'], 'rt_regional_unique');
            $table->foreign('request_type_id')->references('type_id')->on('request_types')->cascadeOnDelete();
            $table->foreign('institution_id')->references('institution_id')->on('institutions')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_type_regional_assignments');
    }
};
