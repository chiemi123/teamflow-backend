<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\Project;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'assigned_user_id' => User::factory(),
            'status_id' => TaskStatus::factory(),
            'project_id' => Project::factory(),
            'organization_id' => Organization::factory(),
            'created_by' => User::factory(),
        ];
    }
}
