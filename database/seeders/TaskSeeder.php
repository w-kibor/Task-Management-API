<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
            ]
        );

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        Task::query()->insert([
            [
                'user_id' => $user->id,
                'title' => 'Review internship assignment brief',
                'due_date' => $today,
                'priority' => 'high',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Implement tasks migration and model',
                'due_date' => $today,
                'priority' => 'medium',
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Prepare API deployment checklist',
                'due_date' => $tomorrow,
                'priority' => 'low',
                'status' => 'done',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
