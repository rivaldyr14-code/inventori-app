<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Category extends Model implements AuditableContract
{
    use HasUuid, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'metadata',
        'attachment_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the metadata attribute, auto-fixing double-encoded JSON.
     */
    public function getMetadataAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        // If it's still a string, decode it
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        // If decoded is still a string (double-encoded), decode again
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Set the metadata attribute as JSON.
     */
    public function setMetadataAttribute($value): void
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->attributes['metadata'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get the products for the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
