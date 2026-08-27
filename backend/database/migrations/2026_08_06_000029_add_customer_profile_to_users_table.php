<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('billing_name')->nullable()->after('address');
            $table->text('tax_id')->nullable()->after('billing_name');
            $table->string('preferred_contact_channel', 20)->default('phone')->after('tax_id');
            $table->string('emergency_contact_name')->nullable()->after('preferred_contact_channel');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('customer_status', 20)->default('active')->after('emergency_contact_phone');
            $table->text('internal_notes')->nullable()->after('customer_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'address',
                'billing_name',
                'tax_id',
                'preferred_contact_channel',
                'emergency_contact_name',
                'emergency_contact_phone',
                'customer_status',
                'internal_notes',
            ]);
        });
    }
};
