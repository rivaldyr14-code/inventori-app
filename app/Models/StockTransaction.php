<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class StockTransaction extends Model implements AuditableContract
{
    use HasUuid, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'created_by',
        'transaction_number',
        'type',
        'quantity',
        'is_active',
        'stock_before',
        'stock_after',
        'notes',
        'metadata',
        'attachment_path',
    ];

    /**
     * Attributes excluded from audit logging.
     * attachment_path is mutable (allowed to change after creation),
     * so we exclude it from audit diffs to reduce noise.
     * Core immutable fields (stock_before, stock_after, quantity, type, product_id)
     * are NOT excluded — they are audited on create and immutability is enforced
     * at the request layer via UpdateStockTransactionRequest.
     *
     * @var array<int, string>
     */
    protected array $auditExclude = ['attachment_path', 'updated_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'is_active'   => 'boolean',
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

        $decoded = is_string($value) ? json_decode($value, true) : $value;

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
     * Get the product associated with this transaction.
     * Uses withTrashed() so the relation still resolves even if the product is soft-deleted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the user who created this transaction.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
