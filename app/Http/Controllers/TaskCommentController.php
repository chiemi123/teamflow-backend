<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Requests\UpdateTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class TaskCommentController extends Controller
{
    public function index(Task $task)
    {
        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->get();

        return TaskCommentResource::collection($comments);
    }

    public function store(
        StoreTaskCommentRequest $request,
        Task $task,
        NotificationService $notificationService
    ): TaskCommentResource {
        $user = Auth::user();

        $comment = $task->comments()->create([
            'organization_id' => $task->organization_id,
            'user_id' => $user->id,
            'content' => $request->validated('content'),
        ]);

        $notificationService->taskCommented($task, $user);

        return new TaskCommentResource($comment->load('user'));
    }

    public function show(TaskComment $comment)
    {
        $comment->load('user');

        return new TaskCommentResource($comment);
    }

    public function update(
        UpdateTaskCommentRequest $request,
        TaskComment $comment
    ): TaskCommentResource {
        $this->authorize('update', $comment);
        $comment->update($request->validated());

        return new TaskCommentResource($comment->load('user'));
    }

    public function destroy(TaskComment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->noContent();
    }
}
