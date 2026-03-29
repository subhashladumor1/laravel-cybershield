<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cybershield_queue_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->string('status', 20);
            $table->float('execution_time')->default(0);
            $table->timestamp('captured_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cybershield_queue_metrics');
    }
};
