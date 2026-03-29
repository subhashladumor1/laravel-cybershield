<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cybershield_system_metrics', function (Blueprint $table) {
            $table->id();
            $table->float('cpu_load')->default(0);
            $table->float('memory_usage')->default(0);
            $table->float('disk_usage')->default(0);
            $table->timestamp('captured_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cybershield_system_metrics');
    }
};
