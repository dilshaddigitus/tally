<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConnectorDevice extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'device_name',
        'status',
        'access_token_hash',
        'refresh_token_hash',
        'token_expires_at',
        'connector_version',
        'last_heartbeat_at',
        'revoked_at',
    ];

    protected $hidden = [
        'access_token_hash',
        'refresh_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function tallyInstallation(): HasOne
    {
        return $this->hasOne(TallyInstallation::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ConnectorLog::class);
    }

    public function syncJobs(): HasMany
    {
        return $this->hasMany(SyncJob::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function tokenExpired(): bool
    {
        return $this->token_expires_at === null || $this->token_expires_at->isPast();
    }
}
