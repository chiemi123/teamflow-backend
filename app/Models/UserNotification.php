<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasOrganization;

class UserNotification extends BaseModel
{
    use HasFactory;
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'task_id',
        'type',
        'message',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
