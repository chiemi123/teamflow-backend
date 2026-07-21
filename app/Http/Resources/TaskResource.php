<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title ?? '',
            'description' => $this->description,
            'status_id' => $this->status_id,
            'assigned_user_id' => $this->assigned_user_id,
            'task_status' => $this->status ? [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ] : null,
            'assigned_user' => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
            ] : null,
            'due_date' => optional($this->due_date)?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'permissions' => [
                'can_update' => $request->user()?->can(
                    'update',
                    $this->resource
                ) ?? false,

                'can_delete' => $request->user()?->can(
                    'delete',
                    $this->resource
                ) ?? false,

                'can_update_status' => $request->user()?->can(
                    'update',
                    $this->resource
                ) ?? false,
            ],
        ];
    }
}
