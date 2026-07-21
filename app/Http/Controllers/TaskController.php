<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::with(['status', 'assignedUser']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {

        $this->authorize('create', Task::class);

        $orgId = $request->user()->current_org_id;

        $defaultStatus = TaskStatus::getDefault($orgId);

        if (!$defaultStatus) {
            return response()->json([
                'message' => 'Default status not found for this organization'
            ], 500);
        }

        $task = Task::create([
            ...$request->validated(),
            'status_id' => $defaultStatus->id,
        ]);

        $task->load(['status', 'assignedUser']);

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['status', 'assignedUser']);

        return new TaskResource($task);
    }

    public function update(
        UpdateTaskRequest $request,
        Task $task,
        NotificationService $notificationService
    ): TaskResource {
        $this->authorize('update', $task);

        $user = Auth::user();

        $task->update($request->validated());

        $notificationService->taskUpdated($task, $user);

        $task->load(['status', 'assignedUser']);

        return new TaskResource($task);
    }

    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Task $task,
        NotificationService $notificationService
    ): TaskResource {
        $this->authorize('update', $task);

        $user = Auth::user();
        $validated = $request->validated();

        $taskStatus = TaskStatus::findOrFail($validated['status_id']);

        $task->status_id = $taskStatus->id;

        if ($taskStatus->name === 'Done') {
            $task->completed_at ??= now();
        } else {
            $task->completed_at = null;
        }

        $task->save();

        $notificationService->taskStatusUpdated($task, $user);

        $task->load(['status', 'assignedUser']);

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
