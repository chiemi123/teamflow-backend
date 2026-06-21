<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
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
        'task_id' => $this->task_id,
        'user' => [
            'id' => $this->user?->id,
            'name' => $this->user?->name,
        ],
        'file_name' => $this->file_name,
        'mime_type' => $this->mime_type,
        'file_size' => $this->file_size,
        'download_url' => url("/api/attachments/{$this->id}/download"),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        ];
    }
}
