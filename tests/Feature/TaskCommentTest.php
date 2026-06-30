<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskComment;
use App\Models\Organization;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_task_comments()
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => 'コメントテスト',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/tasks/{$task->id}/comments");

        $response->assertOk()
            ->assertJsonFragment([
                'content' => 'コメントテスト',
            ]);
    }

    public function test_authenticated_user_can_store_task_comment()
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/tasks/{$task->id}/comments", [
                'content' => '新しいコメント',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('task_comments', [
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '新しいコメント',
        ]);
    }

    public function test_user_cannot_store_comment_to_other_organization_task()
    {
        $organization1 = Organization::factory()->create();

        $organization2 = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization1->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization2->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization2->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization2->id,
            'status_id' => $status->id,
            'assigned_user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/tasks/{$task->id}/comments", [
                'content' => '不正コメント',
            ]);

        $response->assertNotFound();
    }

    public function test_authenticated_user_can_update_task_comment()
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $comment = TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '更新前コメント',
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/task-comments/{$comment->id}", [
                'content' => '更新後コメント',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'content' => '更新後コメント',
        ]);
    }

    public function test_authenticated_user_can_delete_task_comment()
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $comment = TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '削除対象コメント',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/task-comments/{$comment->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('task_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_guest_cannot_access_task_comments_api()
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $comment = TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '未ログインテスト',
        ]);

        $this->getJson("/api/tasks/{$task->id}/comments")
            ->assertUnauthorized();

        $this->postJson("/api/tasks/{$task->id}/comments", [
            'content' => '未ログイン投稿',
        ])->assertUnauthorized();

        $this->putJson("/api/task-comments/{$comment->id}", [
            'content' => '未ログイン更新',
        ])->assertUnauthorized();

        $this->deleteJson("/api/task-comments/{$comment->id}")
            ->assertUnauthorized();
    }

    public function test_notification_is_created_for_assigned_user_when_comment_is_created(): void
    {
        $organization = Organization::factory()->create();

        $commentUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $assignedUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $commentUser->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'assigned_user_id' => $assignedUser->id,
            'created_by' => $commentUser->id,
        ]);

        $response = $this->actingAs($commentUser)->postJson("/api/tasks/{$task->id}/comments", [
            'content' => '確認お願いします。',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $assignedUser->id,
            'task_id' => $task->id,
            'type' => 'task_commented',
            'message' => "タスク「{$task->title}」にコメントが追加されました。",
            'read_at' => null,
        ]);
    }

    public function test_notification_is_not_created_when_comment_user_is_assigned_user(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/tasks/{$task->id}/comments", [
            'content' => '自分の担当タスクにコメントします。',
        ]);

        $response->assertCreated();

        $this->assertDatabaseMissing('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'task_id' => $task->id,
            'type' => 'task_commented',
        ]);
    }
}
