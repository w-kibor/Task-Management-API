<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::query()->create([
            'user_id' => $request->user()->id,
            'title' => $request->string('title')->toString(),
            'due_date' => $request->string('due_date')->toString(),
            'priority' => $request->string('priority')->toString(),
            'status' => 'pending',
        ]);

        return response()->json($task, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'done'])],
        ]);

        $query = Task::query()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $tasks = $query
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'data' => $tasks,
        ]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $allowedProgression = [
            'pending' => 'in_progress',
            'in_progress' => 'done',
            'done' => null,
        ];

        $currentStatus = $task->status;
        $nextExpectedStatus = $allowedProgression[$currentStatus] ?? null;
        $requestedStatus = $request->string('status')->toString();

        if ($requestedStatus !== $nextExpectedStatus) {
            return response()->json([
                'message' => sprintf(
                    'Invalid status transition. Status can only move from %s to %s.',
                    $currentStatus,
                    $nextExpectedStatus ?? 'none'
                ),
            ], 422);
        }

        $task->update([
            'status' => $requestedStatus,
        ]);

        return response()->json($task->fresh());
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        if ($task->status !== 'done') {
            return response()->json([
                'message' => 'Only tasks with done status can be deleted.',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = $request->string('date')->toString();

        $summary = [
            'high' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'low' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
        ];

        $counts = Task::query()
            ->selectRaw('priority, status, COUNT(*) as total')
            ->where('user_id', $request->user()->id)
            ->whereDate('due_date', $date)
            ->groupBy('priority', 'status')
            ->get();

        foreach ($counts as $count) {
            $summary[$count->priority][$count->status] = (int) $count->total;
        }

        return response()->json([
            'data' => [
                'date' => $date,
                'summary' => $summary,
            ],
        ]);
    }
}
