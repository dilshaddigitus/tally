<?php

namespace App\Models\Concerns;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCustomer
{
    protected static function bootBelongsToCustomer(): void
    {
        static::addGlobalScope('customer', function (Builder $builder) {
            $user = Auth::user();

            if ($user === null || $user->isSuperAdmin()) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.customer_id', $user->customer_id);
        });

        static::creating(function ($model) {
            if ($model->customer_id !== null) {
                return;
            }

            $user = Auth::user();

            if ($user !== null && ! $user->isSuperAdmin()) {
                $model->customer_id = $user->customer_id;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Query across every tenant, bypassing the customer scope.
     * Reserved for Super Admin code paths — every call site must be deliberate.
     */
    public function scopeAnyTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('customer');
    }
}
