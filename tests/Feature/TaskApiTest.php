<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_task_endpoints(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
        $this->postJson('/api/tasks', [])->assertUnauthorized();
    }

    public function test_it_creates_a_task_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/tasks', [
            'title' => 'Create API endpoint docs',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
        ]);

        $response
            ->assertCreated()
            ->assertJsonFragment([
                'title' => 'Create API endpoint docs',
                'priority' => 'high',
                'status' => 'pending',
                'user_id' => $user->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Create API endpoint docs',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_title_on_same_date_is_rejected_per_user_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::query()->create([
            'user_id' => $user->id,
            'title' => 'Duplicate constraint test',
            'due_date' => now()->toDateString(),
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->postJson('/api/tasks', [
                'title' => 'Duplicate constraint test',
                'due_date' => now()->toDateString(),
                'priority' => 'high',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->actingAs($otherUser)
            ->postJson('/api/tasks', [
                'title' => 'Duplicate constraint test',
                'due_date' => now()->toDateString(),
                'priority' => 'high',
            ])
            ->assertCreated();
    }

    public function test_it_lists_only_tasks_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::query()->create([
            'user_id' => $user->id,
            'title' => 'My task',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other user task',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/api/tasks');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('My task', $response->json('data.0.title'));
    }

    public function test_it_prevents_updating_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $task = Task::query()->create([
            'user_id' => $owner->id,
            'title' => 'Owner task',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $this->actingAs($intruder)
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'in_progress'])
            ->assertForbidden();
    }

    public function test_it_prevents_deleting_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $task = Task::query()->create([
            'user_id' => $owner->id,
            'title' => 'Owner done task',
            'due_date' => now()->toDateString(),
            'priority' => 'low',
            'status' => 'done',
        ]);

        $this->actingAs($intruder)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_daily_report_returns_counts_for_authenticated_user_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $reportDate = now()->toDateString();

        Task::query()->create([
            'user_id' => $user->id,
            'title' => 'My high pending',
            'due_date' => $reportDate,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other high pending',
            'due_date' => $reportDate,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->getJson('/api/tasks/report?date='.$reportDate);

        $response->assertOk()->assertJson([
            'data' => [
                'date' => $reportDate,
                'summary' => [
                    'high' => ['pending' => 1, 'in_progress' => 0, 'done' => 0],
                    'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
                    'low' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
                ],
            ],
        ]);
    }
}
