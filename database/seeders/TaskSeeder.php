<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        Task::query()->insert([
            [
                'title' => 'Review internship assignment brief',
                'due_date' => $today,
                'priority' => 'high',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Implement tasks migration and model',
                'due_date' => $today,
                'priority' => 'medium',
                'status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
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
