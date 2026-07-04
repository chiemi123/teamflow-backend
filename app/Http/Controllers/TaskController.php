<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;



class TaskController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Task::class);
        $tasks = Task::with(['status', 'assignedUser'])
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

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $task->load(['status', 'assignedUser']);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task, NotificationService $notificationService): TaskResource
    {
        $this->authorize('update', $task);

        $user = Auth::user();

        $task->update($request->validated());

        $notificationService->taskUpdated($task, $user);

        return new TaskResource($task);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task, NotificationService $notificationService): TaskResource
    {
        $this->authorize('update', $task);
        $user = Auth::user();

        $task->status_id = $request->status_id;
        $task->save();

        $notificationService->taskStatusUpdated($task, $user);

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
