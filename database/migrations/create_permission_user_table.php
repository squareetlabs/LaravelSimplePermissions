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
        Schema::create('permission_user', static function (Blueprint $table) {
            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->uuid('id')->primary(),
                'int' => $table->id(),
                default => $table->id(), // bigint
            };

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->foreignUuid('permission_id')->constrained()->cascadeOnDelete(),
                default => $table->foreignId('permission_id')->constrained()->cascadeOnDelete(),
            };

            $table->boolean('forbidden')->default(false)->comment('If true, explicitly denies this permission even if role has it');

            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
