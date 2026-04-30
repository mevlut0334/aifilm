<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create Admin User
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@aifilm.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('271369lmlm'),
            ]
        );

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Admin credentials:');
        $this->command->info('  Email: admin@aifilm.com');
        $this->command->info('  Password: 271369lmlm');
    }
}
