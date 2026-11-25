<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerGroup extends Model
{
    use BelongsToCustomer, HasFactory;

    protected $fillable = [
        'customer_id',
        'tally_company_id',
        'tally_guid',
        'name',
        'parent_tally_guid',
    ];

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }
}
