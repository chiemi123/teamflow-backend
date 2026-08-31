<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganization;

class TaskActivityLog extends BaseModel
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'task_id',
        'user_id',
        'action',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
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
