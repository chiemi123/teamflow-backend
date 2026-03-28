<?php

namespace App\Traits;

/** @var \App\Models\User|null $user */
trait HasCreator
{
    protected static function bootHasCreator()
    {
        static::creating(function ($model) {

            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            if (!$model->created_by && $user) {
                $model->created_by = $user->id;
            }

        });
    }
}