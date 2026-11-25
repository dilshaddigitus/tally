<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'tally_company_id',
        'data_module',
        'last_synced_marker',
    ];

    public function tallyCompany(): BelongsTo
    {
        return $this->belongsTo(TallyCompany::class);
    }
}
