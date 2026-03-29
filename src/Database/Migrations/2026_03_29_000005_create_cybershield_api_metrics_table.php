<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cybershield_api_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->integer('hits')->default(0);
            $table->float('avg_response_time')->default(0);
            $table->timestamp('captured_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cybershield_api_metrics');
    }
};
