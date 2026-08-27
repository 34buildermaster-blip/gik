<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_comments', function (Blueprint $table): void {
            $table->id();
            $table->string('article_slug')->index();
            $table->string('article_title')->nullable();
            $table->string('author_name', 100);
            $table->string('author_email')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_reply')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['article_slug', 'status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comments');
    }
};
