<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\TokenBalance;
use App\Models\User;
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

        // Create Test User
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password'),
            ]
        );

        // Create Token Balance for Test User
        TokenBalance::updateOrCreate(
            ['user_id' => $user->id],
            ['balance' => 1000]
        );

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Admin credentials:');
        $this->command->info('  Email: admin@aifilm.com');
        $this->command->info('  Password: 271369lmlm');
        $this->command->info('');
        $this->command->info('Test user credentials:');
        $this->command->info('  Email: test@example.com');
        $this->command->info('  Password: password');
        $this->command->info('  Token Balance: 1000');
    }
}
