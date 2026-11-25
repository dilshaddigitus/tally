<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SyncJob extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'tally_company_id',
        'connector_device_id',
        'correlation_id',
        'job_type',
        'data_module',
        'status',
        'attempt',
        'max_attempts',
        'date_from',
        'date_to',
        'claimed_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'claimed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $job) {
            $job->correlation_id ??= (string) Str::uuid();
        });
    }

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ConnectorDevice::class, 'connector_device_id');
    }

    public function log(): HasOne
    {
        return $this->hasOne(SyncLog::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(SyncError::class);
    }

    public function canRetry(): bool
    {
        return $this->attempt < $this->max_attempts;
    }
}
