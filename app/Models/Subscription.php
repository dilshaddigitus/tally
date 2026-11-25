<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'billing_type',
        'status',
        'started_at',
        'renews_at',
        'cancelled_at',
        'payment_status',
        'payment_method',
        'external_reference',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'renews_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isOneTime(): bool
    {
        return $this->billing_type === 'one_time';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
