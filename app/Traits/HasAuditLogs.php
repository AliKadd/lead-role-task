<?php

namespace App\Traits;

trait HasAuditLogs
{
    public static function bootHasAuditLogs()
    {
        static::created(function ($model) {
            $model->logAudit('create', $model->toArray());
        });

        static::updated(function ($model) {
            $model->logAudit('update', [
                'before' => $model->getOriginal(),
                'after'  => $model->getAttributes(),
            ]);
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->toArray());
        });
    }
}
