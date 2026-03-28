<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Organization;
use RuntimeException;

class ProjectSeeder extends Seeder
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

        if (!$org1 || !$org2 || !$user1 || !$user2) {
            throw new RuntimeException('Seederの前提データが不足しています');
        }

        Project::updateOrCreate(
            [
                'name' => 'Demo Project',
                'organization_id' => $org1->id,
            ],
            [
                'description' => 'Demo Organization Project',
                'creator_id' => $user1->id,
            ]
        );

        Project::updateOrCreate(
            [
                'name' => 'Other Project',
                'organization_id' => $org2->id,
            ],
            [
                'description' => 'Other Organization Project',
                'creator_id' => $user2->id,
            ]
        );
    }
}
