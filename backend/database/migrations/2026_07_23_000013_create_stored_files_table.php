<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('disk', 32);
            $table->text('path');
            $table->string('original_name');
            $table->string('mime_type', 160);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('visibility', 16)->default('private');
            $table->string('category', 80);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['category', 'created_at']);
        });

        Schema::table('project_update_media', function (Blueprint $table): void {
            $table->foreignId('stored_file_id')
                ->nullable()
                ->after('project_update_id')
                ->constrained('stored_files')
                ->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('avatar_file_id')
                ->nullable()
                ->after('avatar_path')
                ->constrained('stored_files')
                ->nullOnDelete();
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->foreignId('cover_file_id')
                ->nullable()
                ->after('cover_image')
                ->constrained('stored_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cover_file_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('avatar_file_id');
        });

        Schema::table('project_update_media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('stored_file_id');
        });

        Schema::dropIfExists('stored_files');
    }
};
