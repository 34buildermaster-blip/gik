<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->reinterpretSchedule('Asia/Bangkok', 'UTC');
    }

    public function down(): void
    {
        $this->reinterpretSchedule('UTC', 'Asia/Bangkok');
    }

    private function reinterpretSchedule(string $sourceTimezone, string $targetTimezone): void
    {
        DB::table('welcome_popups')
            ->orderBy('id')
            ->eachById(function (object $popup) use ($sourceTimezone, $targetTimezone): void {
                $updates = [];

                foreach (['starts_at', 'ends_at'] as $field) {
                    if ($popup->{$field} !== null) {
                        $updates[$field] = CarbonImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            $popup->{$field},
                            $sourceTimezone,
                        )->setTimezone($targetTimezone)->format('Y-m-d H:i:s');
                    }
                }

                if ($updates !== []) {
                    DB::table('welcome_popups')->where('id', $popup->id)->update($updates);
                }
            });
    }
};
