<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_updates', function (Blueprint $table): void {
            $table->foreignId('project_step_id')
                ->nullable()
                ->after('project_id')
                ->constrained('project_steps')
                ->nullOnDelete();
            $table->string('inspection_result')->nullable()->after('progress_percent');
            $table->text('progress_reason')->nullable()->after('inspection_result');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('submitted_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_updates', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('project_step_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn([
                'inspection_result',
                'progress_reason',
                'submitted_at',
                'reviewed_at',
                'review_note',
            ]);
        });
    }
};
