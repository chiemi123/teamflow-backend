<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Role;
use App\Models\UserNotification;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_tasks_for_current_organization(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $task->id,
                'title' => $task->title,
            ]);
    }

    public function test_user_cannot_get_tasks_from_other_organization(): void
    {
        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $otherOrganization->id,
        ]);

        $otherProject = Project::factory()->create([
            'organization_id' => $otherOrganization->id,
            'created_by' => $otherUser->id,
        ]);

        $otherStatus = TaskStatus::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $otherTask = Task::factory()->create([
            'organization_id' => $otherOrganization->id,
            'project_id' => $otherProject->id,
            'status_id' => $otherStatus->id,
            'created_by' => $otherUser->id,
            'title' => 'Other Organization Task',
        ]);

        $response = $this->actingAs($user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonMissing([
                'id' => $otherTask->id,
                'title' => 'Other Organization Task',
            ]);
    }

    public function test_authenticated_user_can_get_task_detail(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $task->id,
                'title' => $task->title,
            ]);
    }

    public function test_authenticated_user_can_store_task(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/tasks', [
            'project_id' => $project->id,
            'title' => '新規タスク',
            'description' => '新規タスクの説明',
            'assigned_user_id' => null,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('tasks', [
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'title' => '新規タスク',
            'description' => '新規タスクの説明',
            'created_by' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_update_task(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
            'due_date' => null,
        ]);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task->id}", [
            'title' => '更新後タスク',
            'description' => '更新後の説明',
            'assigned_user_id' => null,
            'due_date' => '2026-07-31',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => '更新後タスク',
            'description' => '更新後の説明',
            'due_date' => '2026-07-31',
        ]);
    }

    public function test_authenticated_user_can_delete_task(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $this->seed(RolesSeeder::class);

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();

        $user->organizations()->attach($organization->id, [
            'role_id' => $ownerRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_guest_cannot_access_task_api(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertUnauthorized();
    }

    public function test_notification_is_created_for_assigned_user_when_task_is_updated(): void
    {
        $organization = Organization::factory()->create();

        $actor = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $assignedUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $actor->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $assignedUser->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $actor->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'assigned_user_id' => $assignedUser->id,
            'created_by' => $actor->id,
        ]);

        $response = $this->actingAs($actor)->putJson("/api/tasks/{$task->id}", [
            'project_id' => $project->id,
            'title' => '更新後タスクタイトル',
            'description' => '更新後の説明です',
            'status_id' => $status->id,
            'assigned_user_id' => $assignedUser->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $assignedUser->id,
            'task_id' => $task->id,
            'type' => 'task_updated',
            'message' => 'タスク「更新後タスクタイトル」が更新されました。',
            'read_at' => null,
        ]);
    }

    public function test_notification_is_not_created_when_task_updater_is_assigned_user(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task->id}", [
            'project_id' => $project->id,
            'title' => '自分で更新したタスク',
            'description' => '自分が担当者なので通知は不要',
            'status_id' => $status->id,
            'assigned_user_id' => $user->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('user_notifications', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'task_id' => $task->id,
            'type' => 'task_updated',
        ]);
    }

    public function test_authenticated_user_can_filter_tasks_by_project_id(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $user->update([
            'current_org_id' => $organization->id,
        ]);

        $projectA = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $projectB = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $taskA = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $projectA->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $taskB = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $projectB->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tasks?project_id={$projectA->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $taskA->id,
        ]);

        $response->assertJsonMissing([
            'id' => $taskB->id,
        ]);
    }

    public function test_owner_task_response_includes_expected_permissions(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $this->seed(RolesSeeder::class);

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();

        $user->organizations()->attach($organization->id, [
            'role_id' => $ownerRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', true)
            ->assertJsonPath('data.permissions.can_update_status', true);
    }

    public function test_member_task_response_includes_expected_permissions(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', false)
            ->assertJsonPath('data.permissions.can_update_status', true);
    }
}
