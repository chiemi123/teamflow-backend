<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Organization;

use RuntimeException;


class TaskCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::where('name', 'Demo Organization')->first();

        if (!$org) {
            throw new RuntimeException('Demo Organizationが見つかりません');
        }

        $task = Task::where('organization_id', $org->id)
            ->where('title', 'Demo Task')
            ->first();

        $ownerUser = User::where('email', 'owner@example.com')->first();
        $adminUser = User::where('email', 'admin@example.com')->first();
        $memberUser = User::where('email', 'member@example.com')->first();

        if (
            !$task ||
            !$ownerUser ||
            !$adminUser ||
            !$memberUser
        ) {
            throw new RuntimeException('TaskCommentSeederの前提データが不足しています');
        }

        TaskComment::updateOrCreate(
            [
                'organization_id' => $org->id,
                'task_id' => $task->id,
                'user_id' => $ownerUser->id,
            ],
            [
                'content' => 'Ownerによる確認コメントです。',
            ]
        );

        TaskComment::updateOrCreate(
            [
                'organization_id' => $org->id,
                'task_id' => $task->id,
                'user_id' => $adminUser->id,
            ],
            [
                'content' => 'Adminによる確認コメントです。',
            ]
        );

        TaskComment::updateOrCreate(
            [
                'organization_id' => $org->id,
                'task_id' => $task->id,
                'user_id' => $memberUser->id,
            ],
            [
                'content' => 'Memberによる確認コメントです。',
            ]
        );
    }
}
