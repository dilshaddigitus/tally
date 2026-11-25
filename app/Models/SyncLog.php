<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sync_job_id',
        'records_processed',
        'records_inserted',
        'records_updated',
        'records_skipped',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(SyncJob::class, 'sync_job_id');
    }
}
