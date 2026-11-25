<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TallyCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'tally_installation_id',
        'tally_guid',
        'name',
        'financial_year_start',
    ];

    protected function casts(): array
    {
        return [
            'financial_year_start' => 'date',
        ];
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(TallyInstallation::class, 'tally_installation_id');
    }

    public function companyConnection(): HasOne
    {
        return $this->hasOne(TallyCompanyConnection::class);
    }

    public function syncJobs(): HasMany
    {
        return $this->hasMany(SyncJob::class);
    }

    public function isAuthorized(): bool
    {
        return $this->companyConnection !== null;
    }
}
