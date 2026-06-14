<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskComment;
use App\Models\Organization;
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
}
