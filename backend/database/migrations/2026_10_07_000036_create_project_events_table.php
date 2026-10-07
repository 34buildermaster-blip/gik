<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('site_inspection')->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->string('status', 20)->default('scheduled')->index();
            $table->boolean('customer_visible')->default(true);
            $table->timestamp('reminder_sent_at')->nullable()->index();
            $table->timestamps();
        });

        DB::table('users')->whereNotNull('notification_preferences')->orderBy('id')->each(function ($user): void {
            $preferences = json_decode((string) $user->notification_preferences, true);
            if (! is_array($preferences)) {
                return;
            }

            $preferences['events'] = array_values(array_unique([
                ...($preferences['events'] ?? []),
                'project_event_reminder',
            ]));

            DB::table('users')->where('id', $user->id)->update([
                'notification_preferences' => json_encode($preferences, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_events');
    }
};
