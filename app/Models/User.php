<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'is_active',
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

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isBK(): bool { return $this->role === 'bk'; }
    public function isWaliKelas(): bool { return $this->role === 'wali_kelas'; }
    public function isStaff(): bool { return $this->role === 'staff'; }

    public function canPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array($permission, \Illuminate\Support\Facades\Cache::remember(
            'role_permissions:' . $this->role,
            3600,
            fn () => \DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role', $this->role)
                ->pluck('permissions.key')
                ->toArray()
        ));
    }

    public function recordedViolations()
    {
        return $this->hasMany(Violation::class, 'recorded_by');
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class, 'user_id');
    }

    public function handlingParticipants()
    {
        return $this->hasMany(HandlingParticipant::class);
    }

    public function createdHandlings()
    {
        return $this->hasMany(ViolationHandling::class, 'created_by');
    }
}
