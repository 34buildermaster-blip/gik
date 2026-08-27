<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_steps', function (Blueprint $table): void {
            $table->date('planned_start_date')->nullable()->after('sort_order');
            $table->date('planned_end_date')->nullable()->after('planned_start_date');
            $table->timestamp('actual_completed_at')->nullable()->after('planned_end_date');
        });

        Schema::create('project_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stored_file_id')->constrained('stored_files')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('category', 40)->default('other')->index();
            $table->string('version', 40)->default('1.0');
            $table->string('visibility', 20)->default('staff')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });

        Schema::create('project_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 30)->default('open')->index();
            $table->boolean('customer_visible')->default(true)->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('project_issue_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stored_file_id')->constrained('stored_files')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('contact_leads', function (Blueprint $table): void {
            $table->timestamp('next_follow_up_at')->nullable()->after('contacted_at')->index();
            $table->foreignId('converted_project_id')->nullable()->after('assigned_to')->constrained('projects')->nullOnDelete();
            $table->timestamp('converted_at')->nullable()->after('converted_project_id');
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100)->index();
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('contact_leads', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('converted_project_id');
            $table->dropColumn(['next_follow_up_at', 'converted_at']);
        });

        Schema::dropIfExists('project_issue_media');
        Schema::dropIfExists('project_issues');
        Schema::dropIfExists('project_documents');

        Schema::table('project_steps', function (Blueprint $table): void {
            $table->dropColumn(['planned_start_date', 'planned_end_date', 'actual_completed_at']);
        });
    }
};
