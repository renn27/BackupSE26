<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'google_id', 'avatar',
        'google_refresh_token', 'google_drive_folder_id',
        'role', 'status', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['google_refresh_token'];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
        ];
    }

    // Enkripsi otomatis saat set, dekripsi otomatis saat get
    public function setGoogleRefreshTokenAttribute($value): void
    {
        $this->attributes['google_refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getGoogleRefreshTokenAttribute($value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function villageAssignments()
    {
        return $this->hasMany(UserVillageAssignment::class);
    }

    public function villages()
    {
        return $this->belongsToMany(Village::class, 'user_village_assignments')
            ->withPivot(['assigned_by', 'assigned_at']);
    }

    public function businessStatuses()
    {
        return $this->hasMany(BusinessStatus::class, 'updated_by_user_id');
    }

    public function getTotalStorageUsedAttribute(): int
    {
        return $this->files()->where('status', 'uploaded')->sum('size_bytes');
    }
}
