<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    public const VIEW_DASHBOARD = 'view_dashboard';
    public const VIEW_SALES = 'view_sales';
    public const VIEW_PURCHASES = 'view_purchases';
    public const VIEW_INVENTORY = 'view_inventory';
    public const VIEW_LEDGERS = 'view_ledgers';
    public const VIEW_FINANCIAL_REPORTS = 'view_financial_reports';
    public const EXPORT_REPORTS = 'export_reports';

    protected $fillable = [
        'key',
        'label',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
