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

        $user1 = User::where('email', 'admin@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();

        $ownerRole = Role::where('name', 'Owner')->first();
        $memberRole = Role::where('name', 'Member')->first();

        if (!$org1 || !$org2 || !$user1 || !$user2 || !$ownerRole || !$memberRole) {
            throw new RuntimeException('前提データ不足');
        }

        // admin → Owner（Demo Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org1->id,
                'user_id' => $user1->id
            ],
            [
                'role_id' => $ownerRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // user2 → Member（Other Organization）
        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $org2->id,
                'user_id' => $user2->id
            ],
            [
                'role_id' => $memberRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
