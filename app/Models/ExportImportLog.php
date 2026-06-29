<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportImportLog extends Model
{
    use HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'export_import_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'entity',
        'job_type',
        'status',
        'selected_fields',
        'file_path',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_log',
        'started_at',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selected_fields' => 'array',
            'status'          => 'string',
            'started_at'      => 'datetime',
            'completed_at'    => 'datetime',
        ];
    }

    /**
     * Get the user that triggered this log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
