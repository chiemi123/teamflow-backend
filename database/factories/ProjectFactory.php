<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'organization_id' => Organization::factory(),
            'created_by' => User::factory(),
        ];
    }
}
