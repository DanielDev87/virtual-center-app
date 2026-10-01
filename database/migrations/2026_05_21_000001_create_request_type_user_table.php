<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('request_type_user')) {
            Schema::create('request_type_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('request_type_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['request_type_id', 'user_id']);
                $table->foreign('request_type_id')->references('type_id')->on('request_types')->cascadeOnDelete();
                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            });
        }

        // Backfill existing single-manager assignments for backward compatibility.
        if (Schema::hasTable('request_types') && Schema::hasColumn('request_types', 'gestor_id')) {
            $now = now();
            $requestTypes = DB::table('request_types')
                ->select('type_id', 'gestor_id')
                ->whereNotNull('gestor_id')
                ->get();

            foreach ($requestTypes as $requestType) {
                $exists = DB::table('request_type_user')
                    ->where('request_type_id', $requestType->type_id)
                    ->where('user_id', $requestType->gestor_id)
                    ->exists();

                if (!$exists) {
                    DB::table('request_type_user')->insert([
                        'request_type_id' => $requestType->type_id,
                        'user_id' => $requestType->gestor_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_type_user');
    }
};
