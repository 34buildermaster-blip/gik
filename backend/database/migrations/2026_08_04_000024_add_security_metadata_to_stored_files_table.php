<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stored_files', function (Blueprint $table): void {
            $table->string('sha256', 64)->nullable()->index()->after('size');
            $table->string('scan_status', 32)->default('legacy')->after('sha256');
            $table->timestamp('scanned_at')->nullable()->after('scan_status');
        });
    }

    public function down(): void
    {
        Schema::table('stored_files', function (Blueprint $table): void {
            $table->dropIndex(['sha256']);
            $table->dropColumn(['sha256', 'scan_status', 'scanned_at']);
        });
    }
};
