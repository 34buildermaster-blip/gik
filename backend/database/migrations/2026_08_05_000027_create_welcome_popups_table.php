<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_popups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('desktop_stored_file_id')->constrained('stored_files')->cascadeOnDelete();
            $table->foreignId('mobile_stored_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('name');
            $table->string('alt_text');
            $table->string('link_url', 2048)->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_popups');
    }
};
