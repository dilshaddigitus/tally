<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'business_name',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function connectorDevices(): HasMany
    {
        return $this->hasMany(ConnectorDevice::class);
    }

    public function connectorPairings(): HasMany
    {
        return $this->hasMany(ConnectorPairing::class);
    }

    public function tallyCompanyConnections(): HasMany
    {
        return $this->hasMany(TallyCompanyConnection::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latestOfMany();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
