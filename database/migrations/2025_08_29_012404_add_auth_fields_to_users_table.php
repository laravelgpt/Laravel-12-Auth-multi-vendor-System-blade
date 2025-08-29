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
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp', 6)->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->string('social_id')->nullable()->after('otp_expires_at');
            $table->string('social_provider')->nullable()->after('social_id');
            $table->string('avatar')->nullable()->after('social_provider');
            $table->string('phone')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'otp',
                'otp_expires_at',
                'social_id',
                'social_provider',
                'avatar',
                'phone',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
