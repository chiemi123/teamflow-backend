<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskComment extends BaseModel
{
    use HasFactory;
    use HasOrganization;
    use SoftDeletes;

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
