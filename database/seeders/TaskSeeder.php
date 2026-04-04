<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\Organization;
use App\Models\TaskStatus;
use RuntimeException;

class TaskSeeder extends Seeder
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

        $project1 = Project::where('organization_id', $org1->id)->first();
        $project2 = Project::where('organization_id', $org2->id)->first();

        $status1 = TaskStatus::where('organization_id', $org1->id)->first();
        $status2 = TaskStatus::where('organization_id', $org2->id)->first();

        if (!$org1 || !$org2 || !$user1 || !$user2 || !$project1 || !$project2 || !$status1 || !$status2) {
            throw new RuntimeException('TaskSeederの前提データが不足しています');
        }

        Task::unguarded(function () use ($org1, $org2, $user1, $user2, $project1, $project2, $status1, $status2) {

            Task::updateOrCreate(
                [
                    'title' => 'Demo Task',
                    'organization_id' => $org1->id,
                ],
                [
                    'project_id' => $project1->id,
                    'status_id' => $status1->id,
                    'assigned_user_id' => $user1->id,
                    'created_by' => $user1->id,
                ]
            );

            Task::updateOrCreate(
                [
                    'title' => 'Other Task',
                    'organization_id' => $org2->id,
                ],
                [
                    'project_id' => $project2->id,
                    'status_id' => $status2->id,
                    'assigned_user_id' => $user2->id,
                    'created_by' => $user2->id,
                ]
            );

        });

    }
}
