<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Requests\UpdateTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;

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

    public function store(StoreTaskCommentRequest $request, Task $task)
    {

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'content' => $request->validated('content'),
        ]);

        return new TaskCommentResource($comment->load('user'));
    }

    public function show(TaskComment $comment)
    {
        $comment->load('user');

        return new TaskCommentResource($comment);
    }

    public function update(UpdateTaskCommentRequest $request, TaskComment $comment)
    {
        $comment->update($request->validated());

        return new TaskCommentResource($comment->load('user'));
    }

    public function destroy(TaskComment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }
}