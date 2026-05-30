<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Http\Resources\TaskResource;



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

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->status_id = $request->status_id;
        $task->save();

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
