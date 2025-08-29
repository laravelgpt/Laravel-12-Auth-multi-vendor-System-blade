<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->index();
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->enum('login_type', ['password', 'otp', 'social', 'api'])->default('password');
            $table->enum('status', ['success', 'failed', 'blocked'])->index();
            $table->string('failure_reason')->nullable();
            $table->json('location')->nullable(); // Country, city, etc.
            $table->json('device_info')->nullable(); // Device details
            $table->string('session_id')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'status']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
