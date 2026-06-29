<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'guard_name',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the settings attribute, auto-fixing double-encoded JSON.
     */
    public function getSettingsAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Set the settings attribute as JSON.
     */
    public function setSettingsAttribute($value): void
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->attributes['settings'] = is_array($value) ? json_encode($value) : $value;
    }
}
