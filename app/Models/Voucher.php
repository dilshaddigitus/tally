<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'tally_company_id',
        'tally_guid',
        'voucher_type',
        'voucher_number',
        'voucher_date',
        'narration',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(VoucherEntry::class);
    }
}
