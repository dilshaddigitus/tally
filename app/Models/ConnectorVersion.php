<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectorVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_notes',
        'min_supported',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'min_supported' => 'boolean',
            'released_at' => 'datetime',
        ];
    }
}
