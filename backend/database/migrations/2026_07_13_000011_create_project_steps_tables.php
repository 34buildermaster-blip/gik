<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('weight_percent');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('project_step_progress_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('previous_progress');
            $table->unsignedTinyInteger('new_progress');
            $table->string('inspection_result')->default('not_checked')->index();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_step_progress_logs');
        Schema::dropIfExists('project_steps');
    }
};
