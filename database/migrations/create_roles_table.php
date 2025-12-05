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
        Schema::create('roles', static function (Blueprint $table) {
            match (Config::get('simple-permissions.primary_key.type', 'bigint')) {
                'uuid' => $table->uuid('id')->primary(),
                'int' => $table->id(),
                default => $table->id(), // bigint
            };

            $table->string('code');
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
