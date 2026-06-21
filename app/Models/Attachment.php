<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends BaseModel
{
    use HasOrganization;
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'task_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
