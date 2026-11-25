<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'tally_company_id',
        'tally_guid',
        'name',
        'stock_group_tally_guid',
        'unit',
    ];

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }
}
