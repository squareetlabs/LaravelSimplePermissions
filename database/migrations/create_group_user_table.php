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
        Schema::create('group_user', static function (Blueprint $table) {
            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->uuid('id')->primary(),
                'int' => $table->id(),
                default => $table->id(), // bigint
            };

            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(),
                default => $table->foreignId('user_id')->constrained()->cascadeOnDelete(),
            };

            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->foreignUuid('group_id')->constrained()->cascadeOnDelete(),
                default => $table->foreignId('group_id')->constrained()->cascadeOnDelete(),
            };

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user');
    }
};
