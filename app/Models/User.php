<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'bk', 'wali_kelas', 'staff', 'other', 'kepala_sekolah', 'waka_kesiswaan', 'ketua_tim'];

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'role', 'roles', 'is_active', 'active_session_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'roles' => 'array',
        ];
    }

    /**
     * Daftar role user (multi-role). Kolom `role` lama tetap sebagai fallback.
     */
    public function roleList(): array
    {
        $roles = $this->roles ?? [];
        if (!is_array($roles) || empty($roles)) {
            return [$this->role];
        }
        return array_values(array_unique(array_filter($roles)));
    }

    /**
     * Role utama (pertama) — untuk kompatibilitas kolom `role`.
     */
    public function primaryRole(): string
    {
        return $this->roleList()[0] ?? $this->role;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roleList(), true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->roleList())) > 0;
    }

    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isBK(): bool { return $this->hasRole('bk'); }
    public function isWaliKelas(): bool { return $this->hasRole('wali_kelas'); }
    public function isStaff(): bool { return $this->hasRole('staff'); }
    public function isOther(): bool { return $this->hasRole('other'); }
    public function isKepalaSekolah(): bool { return $this->hasRole('kepala_sekolah'); }
    public function isWakaKesiswaan(): bool { return $this->hasRole('waka_kesiswaan'); }
    public function isKetuaTim(): bool { return $this->hasRole('ketua_tim'); }

    /**
     * Role dengan visibilitas data GLOBAL (semua kelas/siswa).
     * Guru ber-role ganda (mis. staff + wali_kelas) masuk kategori ini.
     */
    public function isGlobalRole(): bool
    {
        return $this->hasAnyRole(['admin', 'bk', 'staff', 'waka_kesiswaan', 'kepala_sekolah', 'ketua_tim']);
    }

    /**
     * Wali kelas MURNI (tanpa role global) → datanya dibatasi ke kelas yang diwalikan.
     */
    public function isScopedWaliKelas(): bool
    {
        return $this->hasRole('wali_kelas') && ! $this->isGlobalRole();
    }

    /** ID kelas yang diwalikan ke user ini. */
    public function homeroomClassIds(): array
    {
        return \App\Models\Classes::where('homeroom_teacher_id', $this->id)->pluck('id')->all();
    }

    /** Boleh tidak user ini mengakses data siswa tertentu? */
    public function canViewStudent(\App\Models\Student|int $student): bool
    {
        if (! $this->isScopedWaliKelas()) {
            return true;
        }
        $classId = is_int($student)
            ? \App\Models\Student::find($student)?->class_id
            : $student->class_id;

        return in_array($classId, $this->homeroomClassIds(), true);
    }

    /**
     * Permission dari SEMUA role user (digabung).
     */
    public function allPermissions(): array
    {
        $perms = [];
        foreach ($this->roleList() as $role) {
            $perms = array_merge($perms, \Illuminate\Support\Facades\Cache::remember(
                'role_permissions:' . $role,
                3600,
                fn () => \DB::table('role_permissions')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->where('role_permissions.role', $role)
                    ->pluck('permissions.key')
                    ->toArray()
            ));
        }
        return array_values(array_unique($perms));
    }

    public function canPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array($permission, $this->allPermissions(), true);
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

    /**
     * Cek apakah token sesi aktif masih "hidup" (session file/DB masih ada).
     * Driver array/lain → dianggap hidup (aman, tolak login ganda).
     */
    public function sessionTokenIsAlive(): bool
    {
        $token = $this->active_session_token;
        if (! $token) {
            return false;
        }

        return match (config('session.driver')) {
            'file' => file_exists(storage_path('framework/sessions/'.$token)),
            'database' => \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))
                ->where('id', $token)
                ->exists(),
            default => true,
        };
    }
}
