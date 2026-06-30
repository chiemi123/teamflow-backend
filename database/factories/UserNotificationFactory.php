<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserNotificationFactory extends Factory
{
    protected $model = UserNotification::class;

    public function definition(): array
    {

        return [
            'organization_id' => 1,
            'user_id' => 1,
            'task_id' => null,
            'type' => 'task_commented',
            'message' => fake()->sentence(),
            'read_at' => null,
        ];
    }
}
