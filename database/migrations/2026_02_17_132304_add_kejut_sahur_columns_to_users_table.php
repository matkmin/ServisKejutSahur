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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user'); // admin (agent) or user
            $table->string('phone_number')->nullable();
            $table->string('referral_code')->nullable()->unique(); // valid for agents
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // for users
            $table->time('sahur_time')->nullable();
            $table->string('status')->default('active'); // active, paused
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['role', 'phone_number', 'referral_code', 'admin_id', 'sahur_time', 'status']);
        });
    }
};
