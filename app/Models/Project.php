<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
