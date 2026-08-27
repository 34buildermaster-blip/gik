<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_updates', function (Blueprint $table): void {
            $table->timestamp('notified_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_updates', function (Blueprint $table): void {
            $table->dropColumn('notified_at');
        });
    }
};
