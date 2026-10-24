<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_HOTEL_OWNER = 'hotel_owner';
    public const ROLE_GENERAL_MANAGER = 'general_manager';
    public const ROLE_BRANCH_MANAGER = 'branch_manager';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'property_id',
        'guest_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function cashierShifts()
    {
        return $this->hasMany(CashierShift::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Senior management with access to system-level controls.
     * Branch managers are intentionally excluded from this method.
     */
    public function isManager(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_HOTEL_OWNER,
            self::ROLE_GENERAL_MANAGER,
            'front_office_manager',
            'accountant',
        ], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isBranchManager(): bool
    {
        return $this->role === self::ROLE_BRANCH_MANAGER;
    }

    /**
     * Users allowed to administer the day-to-day operation of one branch.
     */
    public function canManageBranch(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_HOTEL_OWNER,
            self::ROLE_GENERAL_MANAGER,
            self::ROLE_BRANCH_MANAGER,
            'front_office_manager',
        ], true);
    }

    public function canManageProperty(int $propertyId): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->property_id !== null
            && (int) $this->property_id === $propertyId;
    }

    /**
     * Branch managers may manage branch staff, but may not create or elevate
     * system-wide administrators/owners.
     */

    /**
     * Roles allowed to edit/delete normal branch records.
     */
    public function canManageEntries(): bool
    {
        return $this->is_active && in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_HOTEL_OWNER,
            self::ROLE_GENERAL_MANAGER,
            self::ROLE_BRANCH_MANAGER,
        ], true);
    }

    public function canManageEntryProperty(?int $propertyId): bool
    {
        if (!$this->canManageEntries()) {
            return false;
        }

        if (in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_HOTEL_OWNER,
            self::ROLE_GENERAL_MANAGER,
        ], true)) {
            return true;
        }

        return $propertyId !== null
            && $this->property_id !== null
            && (int) $this->property_id === (int) $propertyId;
    }

    public function canAssignRole(string $role): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->role === self::ROLE_HOTEL_OWNER) {
            return $role !== self::ROLE_SUPER_ADMIN;
        }

        if ($this->role === self::ROLE_GENERAL_MANAGER) {
            return !in_array($role, [self::ROLE_SUPER_ADMIN, self::ROLE_HOTEL_OWNER], true);
        }

        if ($this->isBranchManager()) {
            return !in_array($role, [
                self::ROLE_SUPER_ADMIN,
                self::ROLE_HOTEL_OWNER,
                self::ROLE_GENERAL_MANAGER,
                self::ROLE_BRANCH_MANAGER,
            ], true);
        }

        return false;
    }
}
