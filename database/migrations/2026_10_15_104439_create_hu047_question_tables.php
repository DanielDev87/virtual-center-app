<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('request_type_questions')) {
            Schema::create('request_type_questions', function (Blueprint $table) {
                $table->id('question_id');
                $table->unsignedBigInteger('request_type_id')->index();
                $table->string('label', 255);
                $table->string('field_type', 20)->default('text');
                $table->text('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ticket_answers')) {
            Schema::create('ticket_answers', function (Blueprint $table) {
                $table->id('answer_id');
                $table->unsignedBigInteger('ticket_id')->index();
                $table->unsignedBigInteger('question_id')->nullable()->index();
                $table->string('question_label', 255);
                $table->text('answer_value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_answers');
        Schema::dropIfExists('request_type_questions');
    }
};