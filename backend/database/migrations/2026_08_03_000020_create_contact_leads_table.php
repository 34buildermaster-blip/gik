<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_leads', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('service_type', 80)->nullable();
            $table->text('message')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->text('admin_note')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
