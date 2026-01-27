<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'staf',
        'no_hp',
        'tanggal_lahir',
        'jenis_kelamin',
        'tanggal_masuk',
        'alamat',
        'status_pegawai',
        'role',
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
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'employee_projects')
            ->withPivot('tanggal_mulai', 'tanggal_selesai')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getStafLabelAttribute()
    {
        return match($this->staf) {
            'cleaning_services' => 'Cleaning Services',
            'security_services' => 'Security Services',
            default => $this->staf,
        };
    }

    public function getStatusPegawaiLabelAttribute()
    {
        return match($this->status_pegawai) {
            'aktif' => 'Aktif',
            'non-aktif' => 'Non-Aktif',
            'cuti' => 'Cuti',
            default => $this->status_pegawai,
        };
    }

    public function getJenisKelaminLabelAttribute()
    {
        return match($this->jenis_kelamin) {
            'laki-laki' => 'Laki-laki',
            'perempuan' => 'Perempuan',
            default => $this->jenis_kelamin,
        };
    }

    public function getRoleLabelAttribute()
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'employee' => 'Employee',
            default => $this->role,
        };
    }

    // Helper methods for role checking
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }
}
