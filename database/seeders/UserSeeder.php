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

        // ユーザー①（Owner:Demo Organization）
        User::updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'password' => Hash::make('password'),
                'current_org_id' => $org1->id
            ]
        );

        // ユーザー②（Member:Other Organization）
        User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'User Two',
                'password' => Hash::make('password'),
                'current_org_id' => $org2->id
            ]
        );

        // ユーザー③（Admin:Demo Organization）
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'current_org_id' => $org1->id
            ]
        );

        // ユーザー④（Member:Demo Organization）
        User::updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'password' => Hash::make('password'),
                'current_org_id' => $org1->id
            ]
        );
    }
}
