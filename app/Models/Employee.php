<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nip',
        'staf',
        'email',
        'no_hp',
        'tanggal_lahir',
        'jenis_kelamin',
        'tanggal_masuk',
        'alamat',
        'status_pegawai',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
