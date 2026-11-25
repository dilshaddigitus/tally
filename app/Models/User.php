<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_CUSTOMER_ADMIN = 'customer_admin';
    public const ROLE_CUSTOMER_STAFF = 'customer_staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'customer_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isCustomerAdmin(): bool
    {
        return $this->role === self::ROLE_CUSTOMER_ADMIN;
    }

    public function isCustomerStaff(): bool
    {
        return $this->role === self::ROLE_CUSTOMER_STAFF;
    }

    /**
     * Customer Admin implicitly holds every permission within their own tenant;
     * Customer Staff only what's been explicitly granted (brief §6: configurable per staff member).
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin() || $this->isCustomerAdmin()) {
            return true;
        }

        return $this->relationLoaded('permissions')
            ? $this->permissions->contains('key', $key)
            : $this->permissions()->where('key', $key)->exists();
    }
}
