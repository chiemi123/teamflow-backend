<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_list_returns_task_counts_and_completed_task_counts(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $otherProject = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $todoStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Todo',
            'sort_order' => 1,
        ]);

        $doneStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Done',
            'sort_order' => 4,
        ]);

        // 対象プロジェクトに未完了タスクを2件作成
        Task::factory()->count(2)->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $todoStatus->id,
            'created_by' => $user->id,
        ]);

        // 対象プロジェクトに完了タスクを2件作成
        Task::factory()->count(2)->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $doneStatus->id,
            'created_by' => $user->id,
        ]);

        // 別プロジェクトにも完了タスクを作成
        Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $otherProject->id,
            'status_id' => $doneStatus->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/projects');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $project->id,
                'tasks_count' => 4,
                'completed_tasks_count' => 2,
            ]);
    }


    public function test_project_list_does_not_include_deleted_tasks_in_counts(): void
    {
        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'created_by' => $user->id,
        ]);

        $todoStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Todo',
            'sort_order' => 1,
        ]);

        $doneStatus = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Done',
            'sort_order' => 4,
        ]);

        // 削除しない未完了タスクを1件作成
        Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $todoStatus->id,
            'created_by' => $user->id,
        ]);

        // 削除しない完了タスクを1件作成
        Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $doneStatus->id,
            'created_by' => $user->id,
        ]);

        // 削除対象の未完了タスクを作成
        $deletedTodoTask = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $todoStatus->id,
            'created_by' => $user->id,
        ]);

        // 削除対象の完了タスクを作成
        $deletedDoneTask = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $doneStatus->id,
            'created_by' => $user->id,
        ]);

        // 2件を論理削除
        $deletedTodoTask->delete();
        $deletedDoneTask->delete();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/projects');

        // 削除済みタスクが件数に含まれないことを確認
        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $project->id,
                'tasks_count' => 2,
                'completed_tasks_count' => 1,
            ]);
    }
}
