<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
        ];
    }
}
