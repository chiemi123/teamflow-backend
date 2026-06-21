<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'file_name' => $this->faker->word() . '.pdf',
            'file_path' => 'attachments/' . $this->faker->uuid() . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 500000),
        ];
    }
}
