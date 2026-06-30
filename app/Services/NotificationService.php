<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function notifyAssignedUser(
        Task $task,
        User $actor,
        string $type,
        string $message
    ): void {
        if ($task->assigned_user_id === null) {
            return;
        }

        if ($task->assigned_user_id === $actor->id) {
            return;
        }

        UserNotification::create([
            'organization_id' => $task->organization_id,
            'user_id' => $task->assigned_user_id,
            'task_id' => $task->id,
            'type' => $type,
            'message' => $message,
        ]);
    }

    public function taskCommented(Task $task, User $actor): void
    {
        $this->notifyAssignedUser(
            task: $task,
            actor: $actor,
            type: 'task_commented',
            message: "タスク「{$task->title}」にコメントが追加されました。"
        );
    }

    public function taskStatusUpdated(Task $task, User $actor): void
    {
        $this->notifyAssignedUser(
            task: $task,
            actor: $actor,
            type: 'task_status_updated',
            message: "タスク「{$task->title}」のステータスが変更されました。"
        );
    }

    public function taskUpdated(Task $task, User $actor): void
    {
        $this->notifyAssignedUser(
            task: $task,
            actor: $actor,
            type: 'task_updated',
            message: "タスク「{$task->title}」が更新されました。"
        );
    }

    public function attachmentUploaded(Task $task, User $actor): void
    {
        $this->notifyAssignedUser(
            task: $task,
            actor: $actor,
            type: 'attachment_uploaded',
            message: "タスク「{$task->title}」に添付ファイルが追加されました。"
        );
    }
}
