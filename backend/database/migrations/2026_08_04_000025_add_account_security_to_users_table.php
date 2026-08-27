<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('password');
            $table->timestamp('login_locked_until')->nullable()->after('failed_login_attempts');
            $table->boolean('password_must_change')->default(false)->after('login_locked_until');
            $table->timestamp('password_changed_at')->nullable()->after('password_must_change');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'failed_login_attempts',
                'login_locked_until',
                'password_must_change',
                'password_changed_at',
            ]);
        });
    }
};
