<?php

namespace App\Models;


class TaskStatus extends BaseModel
{
    protected $fillable = [
        'organization_id',
        'name',
        'color',
        'sort_order'
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

