<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('notification_preferences')->nullable()->after('line_recipient_id');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('reviewer_id')
                ->nullable()
                ->after('manager_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        $primaryAdminId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if ($primaryAdminId) {
            DB::table('users')->where('id', $primaryAdminId)->update([
                'notification_preferences' => json_encode([
                    'all_projects' => true,
                ], JSON_THROW_ON_ERROR),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewer_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('notification_preferences');
        });
    }
};
