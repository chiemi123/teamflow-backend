<?php

namespace App\Models;

use App\Traits\HasOrganization;

class Project extends BaseModel
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
