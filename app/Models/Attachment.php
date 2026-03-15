<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasOrganization;

class Attachment extends BaseModel
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'task_id',
        'uploaded_by',
        'file_path',
        'file_name'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
