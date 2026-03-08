<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function project()
    {
        return $this->hasMany(Project::class);
    }
}
