<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Update or create admin users with known passwords
        Admin::updateOrCreate(
            ['email' => 'admin@aifilm.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
