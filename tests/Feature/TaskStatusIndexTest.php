<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatusIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_task_statuses_for_current_organization(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'In Progress',
            'sort_order' => 2,
        ]);

        TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Todo',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/task-statuses');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Todo')
            ->assertJsonPath('data.1.name', 'In Progress')
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_get_task_statuses_from_other_organization(): void
    {
        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Todo',
            'sort_order' => 1,
        ]);

        TaskStatus::factory()->create([
            'organization_id' => $otherOrganization->id,
            'name' => 'Other Todo',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/task-statuses');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Todo');
    }

    public function test_guest_cannot_get_task_statuses(): void
    {
        $response = $this->getJson('/api/task-statuses');

        $response->assertUnauthorized();
    }
}
