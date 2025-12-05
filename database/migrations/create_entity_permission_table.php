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
        Schema::create('entity_permission', static function (Blueprint $table) {
            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->uuid('id')->primary(),
                'int' => $table->id(),
                default => $table->id(), // bigint
            };

            $table->morphs('entity');

            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->foreignUuid('permission_id')->constrained()->cascadeOnDelete(),
                default => $table->foreignId('permission_id')->constrained()->cascadeOnDelete(),
            };
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_permission');
    }
};
