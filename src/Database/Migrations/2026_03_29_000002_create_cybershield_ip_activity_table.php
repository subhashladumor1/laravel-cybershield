<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cybershield_ip_activity', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->integer('total_requests')->default(0);
            $table->integer('blocked_hits')->default(0);
            $table->integer('threat_score')->default(0);
            $table->timestamp('last_activity')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cybershield_ip_activity');
    }
};
