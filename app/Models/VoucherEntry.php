<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherEntry extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'voucher_id',
        'ledger_tally_guid',
        'cost_centre_tally_guid',
        'amount',
        'is_debit',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_debit' => 'boolean',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
