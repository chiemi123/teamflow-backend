<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganization;

class TaskComment extends BaseModel
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'task_id',
        'user_id',
        'content'
    ];

    protected $casts = [
        'created_at' => 'datetime'
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
