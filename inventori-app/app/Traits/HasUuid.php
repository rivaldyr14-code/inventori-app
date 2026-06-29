<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the HasUuid trait.
     * Auto-generates a UUID v4 as the primary key on the creating event.
     */
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the primary key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Determine if the primary key is auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
