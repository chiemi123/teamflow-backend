<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $org1 = Organization::where('name', 'Demo Organization')->first();
        $org2 = Organization::where('name', 'Other Organization')->first();

        if (! $org1 || ! $org2) {
            throw new RuntimeException('OrganizationSeederを先に実行してください');
        }

        // ユーザー①（既存）
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'current_org_id' => $org1->id
            ]
        );

        // ユーザー②（追加）
        User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'User Two',
                'password' => Hash::make('password'),
                'current_org_id' => $org2->id
            ]
        );
    }
}
