<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncError extends Model
{
    use HasFactory;

    protected $fillable = [
        'sync_job_id',
        'error_code',
        'message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(SyncJob::class, 'sync_job_id');
    }
}
