<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_own_notifications(): void
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
            'created_by' => $user->id,
        ]);

        $notification = UserNotification::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'task_id' => $task->id,
            'message' => 'タスクにコメントが追加されました。',
        ]);

        $response = $this->actingAs($user)->getJson('/api/user-notifications');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $notification->id,
                'message' => 'タスクにコメントが追加されました。',
            ]);
    }

    public function test_authenticated_user_cannot_get_other_user_notifications(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        UserNotification::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $otherUser->id,
            'message' => '他ユーザー宛の通知です。',
        ]);

        $response = $this->actingAs($user)->getJson('/api/user-notifications');

        $response->assertOk()
            ->assertJsonMissing([
                'message' => '他ユーザー宛の通知です。',
            ]);
    }

    public function test_authenticated_user_can_mark_own_notification_as_read(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $notification = UserNotification::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/user-notifications/{$notification->id}/read");

        $response->assertNoContent();

        $this->assertDatabaseMissing('user_notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_authenticated_user_cannot_mark_other_user_notification_as_read(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $otherUser = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $notification = UserNotification::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $otherUser->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/user-notifications/{$notification->id}/read");

        $response->assertForbidden();

        $this->assertDatabaseHas('user_notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }
}
