<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TallyInstallation extends Model
{
    use HasFactory;

    protected $fillable = [
        'connector_device_id',
        'detected_version',
        'os_info',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(ConnectorDevice::class, 'connector_device_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(TallyCompany::class);
    }
}
