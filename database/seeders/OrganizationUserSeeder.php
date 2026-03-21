<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $user = User::where('email', 'admin@example.com')->first();
        $organization = Organization::first();
        $role = Role::where('name', 'Owner')->first();

        if (! $user) {
            throw new RuntimeException('admin@example.com のユーザーが存在しません。先に UserSeeder を実行してください。');
        }

        if (! $organization) {
            throw new RuntimeException('Demo Organization が存在しません。先に OrganizationSeeder を実行してください。');
        }

        if (! $role) {
            throw new RuntimeException('Owner ロールが存在しません。先に RolesSeeder を実行してください。');
        }

        DB::table('organization_user')->updateOrInsert(
            [
                'organization_id' => $organization->id,
                'user_id' => $user->id
            ],
            [
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
