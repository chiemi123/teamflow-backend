<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class,'organization_user')
                    ->withPivot('role_id');
    }

    public function project()
    {
        return $this->hasMany(Project::class);
    }
}
