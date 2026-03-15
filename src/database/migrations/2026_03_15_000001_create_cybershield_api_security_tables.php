<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('cybershield.api_security.keys_table', 'api_keys');

        Schema::create($tableNames, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('secret', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_concurrent')->default(5);
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('event_type');
            $table->json('metadata')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('cybershield.api_security.keys_table', 'api_keys'));
        Schema::dropIfExists('security_logs');
    }
};
