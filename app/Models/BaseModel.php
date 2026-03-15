<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TenantScope;

class BaseModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}