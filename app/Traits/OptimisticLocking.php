<?php

namespace App\Traits;

use App\Exceptions\OptimisticLockException;

trait OptimisticLocking
{
    public function updateWithLock(array $attributes, int $version)
    {
        $attributes['version'] = $version + 1;

        $updated = static::query()
            ->where($this->getKeyName(), $this->getKey())
            ->where('version', $version)
            ->update($attributes);

        if ($updated === 0) {
            throw new OptimisticLockException(
                "Update conflict exception " . static::class
            );
        }

        $this->refresh();

        return true;
    }
}
