<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'label' => $this->faker->word,
            'color' => '#' . substr(md5($this->faker->hexcolor), 0, 6),
            'organization_id' => 1,
            'sort_order' => 1,
            'is_closed' => false,
            'is_default' => false,
        ];
    }
}
