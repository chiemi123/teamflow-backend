<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->currentRole()?->name,        // 役割名
            'can_create_project' => $this->isOwner() || $this->isAdmin(), // 新規作成権限
            'can_edit_project' => $this->isOwner() || $this->isAdmin(), // 編集権限
            'can_delete_project' => $this->isOwner() || $this->isAdmin(), // 削除権限
            'can_create_task' => $request->user()?->can('create', Task::class) ?? false,
        ];
    }
}
