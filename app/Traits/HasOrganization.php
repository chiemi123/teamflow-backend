<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasOrganization
{
    protected static function bootHasOrganization()
    {
        static::creating(function ($model) {

            if (Auth::check()) {
                $model->organization_id = Auth::user()->current_org_id;
            }

        });
    }
}