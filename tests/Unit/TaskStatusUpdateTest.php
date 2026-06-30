<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Project;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorized_user_can_update_task_status()
    {
        // ① Organization 作成
        $org = Organization::factory()->create();

        // ② 権限ありユーザー作成
        $ownerUser = User::factory()->create([
            'current_org_id' => $org->id
        ]);

        // ③ Project 作成
        $project = Project::factory()->create([
            'organization_id' => $org->id,
            'created_by' => $ownerUser->id
        ]);

        // ④ TaskStatus 作成
        $status = TaskStatus::factory()->create([
            'organization_id' => $org->id
        ]);

        // ⑤ Task 作成（作成者 = 権限ありユーザー）
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status_id' => $status->id,
            'organization_id' => $org->id,
            'created_by' => $ownerUser->id
        ]);

        // ⑥ 新しい TaskStatus 作成
        $newStatus = TaskStatus::factory()->create([
            'organization_id' => $org->id
        ]);

        // ⑦ PUT リクエストでステータス更新
        $response = $this->actingAs($ownerUser, 'sanctum')->putJson("/api/tasks/{$task->id}/status", [
            'status_id' => $newStatus->id
        ]);

        // ⑧ JSON 構造を確認
        $response->assertStatus(200);
        $this->assertEquals(
            $newStatus->id,
            $response->json('data.task_status.id')
        );

        // ⑨ DB に反映されているか確認
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => $newStatus->id
        ]);
    }

    /** @test */
    public function user_from_other_organization_cannot_update_task_status()
    {
        // ① 組織作成
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        // ② 権限ありユーザー作成（org1）
        $ownerUser = User::factory()->create([
            'current_org_id' => $org1->id
        ]);

        // ③ 他組織のユーザー作成（org2）
        $otherUser = User::factory()->create([
            'current_org_id' => $org2->id
        ]);

        // ④ Project 作成
        $project = Project::factory()->create([
            'organization_id' => $org1->id,
            'created_by' => $ownerUser->id
        ]);

        // ⑤ TaskStatus 作成
        $status = TaskStatus::factory()->create([
            'organization_id' => $org1->id
        ]);

        // ⑥ Task 作成（作成者 = org1 のユーザー）
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status_id' => $status->id,
            'organization_id' => $org1->id,
            'created_by' => $ownerUser->id
        ]);

        // ⑦ 新しい TaskStatus 作成
        $newStatus = TaskStatus::factory()->create([
            'organization_id' => $org1->id
        ]);

        // ⑧ 他組織ユーザーで PUT リクエスト
        $response = $this->actingAs($otherUser, 'sanctum')->putJson("/api/tasks/{$task->id}/status", [
            'status_id' => $newStatus->id
        ]);

        // ⑨ 他組織なので 404 Not Found を確認
        $response->assertStatus(404);
    }

    public function test_notification_is_created_for_assigned_user_when_task_status_is_updated(): void
    {
        $organization = Organization::factory()->create();

        $actor = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $assignedUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $actor->id,
        ]);

        $oldStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $oldStatus->id,
            'assigned_user_id' => $assignedUser->id,
            'created_by' => $actor->id,
        ]);

        $response = $this->actingAs($actor)->putJson("/api/tasks/{$task->id}/status", [
            'status_id' => $newStatus->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $assignedUser->id,
            'task_id' => $task->id,
            'type' => 'task_status_updated',
            'message' => "タスク「{$task->title}」のステータスが変更されました。",
            'read_at' => null,
        ]);
    }

    public function test_notification_is_not_created_when_status_updater_is_assigned_user(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $oldStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $newStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $oldStatus->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task->id}/status", [
            'status_id' => $newStatus->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'task_id' => $task->id,
            'type' => 'task_status_updated',
        ]);
    }
}
