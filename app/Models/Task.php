<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganization;
use App\Traits\HasCreator;

class Task extends BaseModel
{
    use HasOrganization;
    use HasCreator;
    use SoftDeletes; //追加

    protected $fillable = [
        'project_id',
        'status_id',
        'title',
        'description',
        'assigned_user_id',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(TaskActivityLog::class);
    }
}
