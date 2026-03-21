<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\TaskStatus;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();

        $statuses = [
            ['name' => 'Todo', 'label' => '未着手', 'color' => '#6B7280', 'sort_order' => 1],
            ['name' => 'In Progress', 'label' => '進行中', 'color' => '#3B82F6', 'sort_order' => 2],
            ['name' => 'Review', 'label' => 'レビュー中', 'color' => '#F59E0B', 'sort_order' => 3],
            ['name' => 'Done', 'label' => '完了', 'color' => '#10B981', 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            TaskStatus::updateOrCreate(
                [
                    'name' => $status['name'],
                    'organization_id' => $organization->id, // ← 追加（超重要）
                ],
                [
                    'label' => $status['label'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'organization_id' => $organization->id, // ← ここにも必要
                ]
            );
        }
    }
}
