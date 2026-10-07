<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_updates', function (Blueprint $table): void {
            $table->boolean('requires_acknowledgement')->default(false)->after('notified_at');
        });

        Schema::table('project_documents', function (Blueprint $table): void {
            $table->boolean('requires_acknowledgement')->default(false)->after('notes');
        });

        Schema::create('project_acknowledgements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->timestamp('acknowledged_at');
            $table->timestamps();
            $table->unique(['user_id', 'target_type', 'target_id'], 'project_ack_user_target_unique');
            $table->index(['project_id', 'target_type', 'target_id'], 'project_ack_target_index');
        });

        Schema::create('project_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_update_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('project_inquiry_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('project_inquiry_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_inquiry_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stored_file_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('users')->whereNotNull('notification_preferences')->orderBy('id')->each(function ($user): void {
            $preferences = json_decode((string) $user->notification_preferences, true);
            if (! is_array($preferences)) {
                return;
            }

            $event = in_array($user->role, ['admin', 'inspector'], true)
                ? 'customer_message_received'
                : 'staff_message_received';
            $events = array_values(array_unique([...($preferences['events'] ?? []), $event]));
            $preferences['events'] = $events;
            DB::table('users')->where('id', $user->id)->update([
                'notification_preferences' => json_encode($preferences, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_inquiry_attachments');
        Schema::dropIfExists('project_inquiry_messages');
        Schema::dropIfExists('project_inquiries');
        Schema::dropIfExists('project_acknowledgements');

        Schema::table('project_documents', function (Blueprint $table): void {
            $table->dropColumn('requires_acknowledgement');
        });

        Schema::table('project_updates', function (Blueprint $table): void {
            $table->dropColumn('requires_acknowledgement');
        });
    }
};
