<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_task(): void
    {
        $response = $this->postJson('/api/tasks', [
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
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Create API endpoint docs',
            'priority' => 'high',
            'status' => 'pending',
        ]);
    }

    public function test_it_rejects_duplicate_title_for_same_due_date(): void
    {
        Task::query()->create([
            'title' => 'Duplicate constraint test',
            'due_date' => now()->toDateString(),
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Duplicate constraint test',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['title']);
    }

    public function test_it_rejects_due_date_in_the_past(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Past due date test',
            'due_date' => now()->subDay()->toDateString(),
            'priority' => 'medium',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['due_date']);
    }

    public function test_it_lists_tasks_sorted_by_priority_then_due_date(): void
    {
        Task::query()->create([
            'title' => 'Low task',
            'due_date' => now()->toDateString(),
            'priority' => 'low',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'title' => 'Medium task',
            'due_date' => now()->addDay()->toDateString(),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'title' => 'High early',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'title' => 'High later',
            'due_date' => now()->addDays(2)->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $this->assertSame('High early', $response->json('0.title'));
        $this->assertSame('High later', $response->json('1.title'));
        $this->assertSame('Medium task', $response->json('2.title'));
        $this->assertSame('Low task', $response->json('3.title'));
    }

    public function test_it_can_filter_list_by_status(): void
    {
        Task::query()->create([
            'title' => 'Pending task',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'title' => 'Done task',
            'due_date' => now()->toDateString(),
            'priority' => 'medium',
            'status' => 'done',
        ]);

        $response = $this->getJson('/api/tasks?status=done');

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertSame('Done task', $response->json('0.title'));
    }

    public function test_it_returns_meaningful_json_when_no_tasks_exist(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertOk()->assertExactJson([
            'message' => 'No tasks found.',
            'data' => [],
        ]);
    }

    public function test_it_allows_only_forward_status_progression(): void
    {
        $task = Task::query()->create([
            'title' => 'Status progression task',
            'due_date' => now()->toDateString(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'done',
        ]);

        $response->assertUnprocessable();

        $this->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'in_progress',
        ])->assertOk();

        $this->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'done',
        ])->assertOk();
    }

    public function test_it_only_deletes_done_tasks(): void
    {
        $inProgressTask = Task::query()->create([
            'title' => 'Cannot delete me yet',
            'due_date' => now()->toDateString(),
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);

        $doneTask = Task::query()->create([
            'title' => 'Can delete me',
            'due_date' => now()->toDateString(),
            'priority' => 'low',
            'status' => 'done',
        ]);

        $this->deleteJson("/api/tasks/{$inProgressTask->id}")
            ->assertForbidden();

        $this->deleteJson("/api/tasks/{$doneTask->id}")
            ->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $inProgressTask->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $doneTask->id]);
    }

    public function test_it_returns_daily_report_with_counts_per_priority_and_status(): void
    {
        $reportDate = now()->toDateString();

        Task::query()->create([
            'title' => 'High pending',
            'due_date' => $reportDate,
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Task::query()->create([
            'title' => 'Medium done',
            'due_date' => $reportDate,
            'priority' => 'medium',
            'status' => 'done',
        ]);

        Task::query()->create([
            'title' => 'Low in progress',
            'due_date' => $reportDate,
            'priority' => 'low',
            'status' => 'in_progress',
        ]);

        $response = $this->getJson('/api/tasks/report?date='.$reportDate);

        $response->assertOk()->assertJson([
            'date' => $reportDate,
            'summary' => [
                'high' => ['pending' => 1, 'in_progress' => 0, 'done' => 0],
                'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 1],
                'low' => ['pending' => 0, 'in_progress' => 1, 'done' => 0],
            ],
        ]);
    }
}
