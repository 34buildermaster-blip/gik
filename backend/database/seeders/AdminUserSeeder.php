<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'Admin34'],
            [
                'name' => 'Admin34',
                'email' => 'admin34@34buildmaster.local',
                'password' => '34Admin.',
            ],
        );
    }
}
