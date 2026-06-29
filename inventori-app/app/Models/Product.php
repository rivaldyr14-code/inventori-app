<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Product extends Model implements AuditableContract
{
    use HasUuid, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'price',
        'current_stock',
        'is_active',
        'extra_data',
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
            'is_active'     => 'boolean',
            'price'         => 'decimal:2',
            'current_stock' => 'integer',
        ];
    }

    /**
     * Get the extra_data attribute, auto-fixing double-encoded JSON.
     */
    public function getExtraDataAttribute($value)
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
     * Set the extra_data attribute as JSON.
     */
    public function setExtraDataAttribute($value): void
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->attributes['extra_data'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock transactions for the product.
     */
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
}
