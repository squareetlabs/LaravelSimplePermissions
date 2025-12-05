<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_user', static function (Blueprint $table) {
            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->uuid('id')->primary(),
                'int' => $table->id(),
                default => $table->id(), // bigint
            };

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->foreignUuid('role_id')->constrained()->cascadeOnDelete(),
                default => $table->foreignId('role_id')->constrained()->cascadeOnDelete(),
            };

            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
