<?php

namespace App\Models;

use App\Traits\HasOrganization;
use App\Traits\HasCreator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends BaseModel
{
    use HasOrganization;
    use HasCreator;
    use SoftDeletes;
    use HasFactory;


    protected $fillable = [
        'name',
        'description',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
