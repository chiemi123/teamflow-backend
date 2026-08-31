<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskComment;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_task_comments(): void
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
            ->assertJsonPath('data.0.content', 'コメントテスト')
            ->assertJsonPath('data.0.permissions.can_update', true)
            ->assertJsonPath('data.0.permissions.can_delete', true);
    }

    public function test_authenticated_user_can_store_task_comment(): void
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

        $response->assertCreated()
            ->assertJsonPath('data.content', '新しいコメント')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', true);

        $this->assertDatabaseHas('task_comments', [
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '新しいコメント',
        ]);
    }

    public function test_user_cannot_store_comment_to_other_organization_task(): void
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

        $this->assertDatabaseMissing('task_comments', [
            'organization_id' => $organization2->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => '不正コメント',
        ]);
    }

    public function test_comment_author_can_update_own_task_comment(): void
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

        $response->assertOk()
            ->assertJsonPath('data.content', '更新後コメント')
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', true);

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'content' => '更新後コメント',
        ]);
    }

    public function test_user_cannot_update_comment_created_by_another_user(): void
    {
        $organization = Organization::factory()->create();

        $commentAuthor = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $commentAuthor->id,
            'created_by' => $commentAuthor->id,
        ]);

        $comment = TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'content' => '更新前コメント',
        ]);

        $response = $this->actingAs($otherUser)
            ->putJson("/api/task-comments/{$comment->id}", [
                'content' => '不正に更新されたコメント',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'content' => '更新前コメント',
        ]);

        $this->assertDatabaseMissing('task_comments', [
            'id' => $comment->id,
            'content' => '不正に更新されたコメント',
        ]);
    }

    public function test_comment_author_can_delete_own_task_comment(): void
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

    public function test_user_cannot_delete_comment_created_by_another_user(): void
    {
        $organization = Organization::factory()->create();

        $commentAuthor = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $commentAuthor->id,
            'created_by' => $commentAuthor->id,
        ]);

        $comment = TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'content' => '削除されてはいけないコメント',
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/task-comments/{$comment->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'content' => '削除されてはいけないコメント',
            'deleted_at' => null,
        ]);
    }

    public function test_comment_permissions_are_false_for_another_user(): void
    {
        $organization = Organization::factory()->create();

        $commentAuthor = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'status_id' => $status->id,
            'assigned_user_id' => $commentAuthor->id,
            'created_by' => $commentAuthor->id,
        ]);

        TaskComment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'content' => '他ユーザーのコメント',
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/tasks/{$task->id}/comments");

        $response->assertOk()
            ->assertJsonPath('data.0.content', '他ユーザーのコメント')
            ->assertJsonPath('data.0.permissions.can_update', false)
            ->assertJsonPath('data.0.permissions.can_delete', false);
    }

    public function test_guest_cannot_access_task_comments_api(): void
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

        $response = $this->actingAs($commentUser)
            ->postJson("/api/tasks/{$task->id}/comments", [
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

        $response = $this->actingAs($user)
            ->postJson("/api/tasks/{$task->id}/comments", [
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
