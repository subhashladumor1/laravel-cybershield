<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cybershield_requests_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->string('method', 10);
            $table->text('url');
            $table->text('user_agent')->nullable();
            $table->integer('status_code');
            $table->float('response_time')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cybershield_requests_logs');
    }
};
