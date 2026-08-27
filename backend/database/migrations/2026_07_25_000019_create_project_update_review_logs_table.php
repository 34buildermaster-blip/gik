<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_update_review_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_update_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['project_update_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_update_review_logs');
    }
};
