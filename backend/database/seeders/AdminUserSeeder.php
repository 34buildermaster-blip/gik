<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! filter_var(env('SEED_STAFF_ACCOUNTS', false), FILTER_VALIDATE_BOOL)) {
            $this->command?->info('Staff account seeding is disabled.');

            return;
        }

        $adminPassword = env('SEED_ADMIN_PASSWORD');
        $inspectorPassword = env('SEED_INSPECTOR_PASSWORD');

        if (! is_string($adminPassword) || mb_strlen($adminPassword) < 12
            || ! is_string($inspectorPassword) || mb_strlen($inspectorPassword) < 12) {
            throw new RuntimeException(
                'SEED_ADMIN_PASSWORD and SEED_INSPECTOR_PASSWORD must each contain at least 12 characters.',
            );
        }

        User::updateOrCreate(
            ['username' => env('SEED_ADMIN_USERNAME', 'Admin34')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Admin34'),
                'email' => env('SEED_ADMIN_EMAIL', 'admin34@34buildmaster.local'),
                'password' => $adminPassword,
                'password_must_change' => true,
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['username' => env('SEED_INSPECTOR_USERNAME', 'Inspector01')],
            [
                'name' => env('SEED_INSPECTOR_NAME', 'ผู้ตรวจหน้างาน 01'),
                'email' => env('SEED_INSPECTOR_EMAIL', 'inspector01@34buildmaster.local'),
                'password' => $inspectorPassword,
                'password_must_change' => true,
                'role' => 'inspector',
            ],
        );
    }
}
