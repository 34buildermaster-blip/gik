<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('terms_accepted_at')->nullable()->after('password_changed_at');
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_accepted_at');
            $table->timestamp('marketing_consent_at')->nullable()->after('privacy_accepted_at');
            $table->string('policy_version', 32)->nullable()->after('marketing_consent_at');
            $table->string('consent_ip_hash', 64)->nullable()->after('policy_version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'terms_accepted_at',
                'privacy_accepted_at',
                'marketing_consent_at',
                'policy_version',
                'consent_ip_hash',
            ]);
        });
    }
};
