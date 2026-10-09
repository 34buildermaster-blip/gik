<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_events', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('customer_response', 32)->nullable()->after('customer_visible');
            $table->foreignId('customer_responded_by')->nullable()->after('customer_response')->constrained('users')->nullOnDelete();
            $table->timestamp('customer_responded_at')->nullable()->after('customer_responded_by');
            $table->text('customer_response_note')->nullable()->after('customer_responded_at');
            $table->timestamp('proposed_starts_at')->nullable()->after('customer_response_note');
        });

        Schema::create('project_event_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stored_file_id')->constrained('stored_files')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_event_id', 'created_at']);
        });

        $this->updateStoredNotificationPreferences(true);
    }

    public function down(): void
    {
        $this->updateStoredNotificationPreferences(false);
        Schema::dropIfExists('project_event_attachments');

        Schema::table('project_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_responded_by');
            $table->dropColumn([
                'latitude',
                'longitude',
                'customer_response',
                'customer_responded_at',
                'customer_response_note',
                'proposed_starts_at',
            ]);
        });
    }

    private function updateStoredNotificationPreferences(bool $adding): void
    {
        $newEvents = [
            'admin' => ['project_event_response', 'daily_operations_summary', 'system_health_alert'],
            'inspector' => ['project_event_response'],
            'user' => [],
        ];

        DB::table('users')->whereNotNull('notification_preferences')->orderBy('id')->eachById(function ($user) use ($adding, $newEvents): void {
            $preferences = json_decode((string) $user->notification_preferences, true);
            if (! is_array($preferences) || ! isset($preferences['events']) || ! is_array($preferences['events'])) {
                return;
            }

            $events = $newEvents[$user->role] ?? [];
            $preferences['events'] = $adding
                ? array_values(array_unique(array_merge($preferences['events'], $events)))
                : array_values(array_diff($preferences['events'], $events));
            DB::table('users')->where('id', $user->id)->update([
                'notification_preferences' => json_encode($preferences, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }
};
