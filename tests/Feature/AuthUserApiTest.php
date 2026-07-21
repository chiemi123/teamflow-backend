<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_get_current_user_with_expected_permissions(): void
    {
        $organization = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $user->organizations()->attach($organization->id, [
            'role_id' => $ownerRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', 'Owner')
            ->assertJsonPath('data.can_create_project', true)
            ->assertJsonPath('data.can_edit_project', true)
            ->assertJsonPath('data.can_delete_project', true)
            ->assertJsonPath('data.can_create_task', true);
    }

    public function test_admin_can_get_current_user_with_expected_permissions(): void
    {
        $organization = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $user->organizations()->attach($organization->id, [
            'role_id' => $adminRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.role', 'Admin')
            ->assertJsonPath('data.can_create_project', true)
            ->assertJsonPath('data.can_edit_project', true)
            ->assertJsonPath('data.can_delete_project', true)
            ->assertJsonPath('data.can_create_task', true);
    }

    public function test_member_can_get_current_user_with_expected_permissions(): void
    {
        $organization = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $user->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.role', 'Member')
            ->assertJsonPath('data.can_create_project', false)
            ->assertJsonPath('data.can_edit_project', false)
            ->assertJsonPath('data.can_delete_project', false)
            ->assertJsonPath('data.can_create_task', true);
    }

    public function test_user_response_uses_role_of_current_organization(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();
        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organizationB->id,
        ]);

        $user->organizations()->attach($organizationA->id, [
            'role_id' => $ownerRole->id,
        ]);

        $user->organizations()->attach($organizationB->id, [
            'role_id' => $memberRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.role', 'Member')
            ->assertJsonPath('data.can_create_project', false)
            ->assertJsonPath('data.can_edit_project', false)
            ->assertJsonPath('data.can_delete_project', false)
            ->assertJsonPath('data.can_create_task', true);
    }

    public function test_guest_cannot_access_user_api(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }
}
