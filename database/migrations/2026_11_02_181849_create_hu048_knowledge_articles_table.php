<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('knowledge_articles')) {
            Schema::create('knowledge_articles', function (Blueprint $table) {
                $table->id('article_id');
                $table->string('title', 255);
                $table->string('summary', 500)->nullable();
                $table->text('steps');
                $table->text('body')->nullable();
                $table->unsignedBigInteger('request_type_id')->nullable()->index();
                $table->string('task', 40)->nullable()->index();
                $table->string('keywords', 500)->nullable();
                $table->string('screen_key', 40)->nullable();
                $table->unsignedBigInteger('owner_id')->nullable()->index();
                $table->string('status', 15)->default('draft')->index();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('last_reviewed_at')->nullable();
                $table->date('valid_until')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};