<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrganizationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org1 = Organization::where('name', 'Demo Organization')->first();
        $org2 = Organization::where('name', 'Other Organization')->first();

        $ownerUser = User::where('email', 'owner@example.com')->first();
        $adminUser = User::where('email', 'admin@example.com')->first();
        $memberUser = User::where('email', 'member@example.com')->first();
        $otherMemberUser = User::where('email', 'user2@example.com')->first();

        $ownerRole = Role::where('name', 'Owner')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $memberRole = Role::where('name', 'Member')->first();

        if (
            !$org1 ||
            !$org2 ||
            !$ownerUser ||
            !$adminUser ||
            !$memberUser ||
            !$otherMemberUser ||
            !$ownerRole ||
            !$adminRole ||
            !$memberRole
        ) {
            throw new RuntimeException('前提データ不足');
        }

        // owner → Owner（Demo Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org1->id,
                'user_id' => $ownerUser->id
            ],
            [
                'role_id' => $ownerRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // admin → Admin（Demo Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org1->id,
                'user_id' => $adminUser->id
            ],
            [
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // member → Member（Demo Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org1->id,
                'user_id' => $memberUser->id
            ],
            [
                'role_id' => $memberRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // user2 → Member（Other Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org2->id,
                'user_id' => $otherMemberUser->id
            ],
            [
                'role_id' => $memberRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
