<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_members_of_current_organization(): void
    {
        $organization = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $member = User::factory()->create();

        $user->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $member->organizations()->attach($organization->id, [
            'role_id' => $memberRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/organization-members');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->assertJsonFragment([
                'id' => $member->id,
                'name' => $member->name,
            ]);
    }

    public function test_members_of_other_organization_are_not_included(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        $this->seed(RolesSeeder::class);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $user = User::factory()->create([
            'current_org_id' => $organizationA->id,
        ]);

        $sameOrganizationMember = User::factory()->create();
        $otherOrganizationMember = User::factory()->create();

        $user->organizations()->attach($organizationA->id, [
            'role_id' => $memberRole->id,
        ]);

        $sameOrganizationMember->organizations()->attach($organizationA->id, [
            'role_id' => $memberRole->id,
        ]);

        $otherOrganizationMember->organizations()->attach($organizationB->id, [
            'role_id' => $memberRole->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/organization-members');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $sameOrganizationMember->id,
                'name' => $sameOrganizationMember->name,
            ])
            ->assertJsonMissing([
                'id' => $otherOrganizationMember->id,
                'name' => $otherOrganizationMember->name,
            ]);
    }

    public function test_guest_cannot_access_organization_members_api(): void
    {
        $response = $this->getJson('/api/organization-members');

        $response->assertUnauthorized();
    }
}
