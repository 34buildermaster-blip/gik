<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('disabled_at')->nullable()->after('login_locked_until')->index();
            $table->foreignId('disabled_by')
                ->nullable()
                ->after('disabled_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('disabled_reason', 500)->nullable()->after('disabled_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('disabled_by');
            $table->dropColumn(['disabled_at', 'disabled_reason']);
        });
    }
};
