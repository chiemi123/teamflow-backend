<?php

namespace App\Traits;


/** @var \App\Models\User|null $user */
trait HasOrganization
{
    protected static function bootHasOrganization()
    {
        static::creating(function ($model) {

            /** @var \App\Models\User|null $user */

            $user = auth()->user();

            if ($user) {
                $model->organization_id = $user->current_org_id;
            }
        });
    }
}
