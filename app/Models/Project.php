<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    protected $fillable = [
        'nama_project',
        'jenis_project',
        'alamat_lengkap',
        'nilai_kontrak',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_masuk',
        'jam_keluar',
        'status',
    ];

    protected $casts = [
        'nilai_kontrak' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(ProjectRoom::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(
            TaskList::class,
            ProjectRoom::class,
            'project_id',
            'project_room_id',
            'id',
            'id'
        );
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_projects')
            ->withPivot('tanggal_mulai', 'tanggal_selesai')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function patrolAreas(): HasMany
    {
        return $this->hasMany(PatrolArea::class);
    }

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }

    public function pics(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_pics')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(ProjectShift::class);
    }

    public function getNilaiKontrakRupiahAttribute()
    {
        return 'Rp ' . number_format($this->nilai_kontrak, 2, ',', '.');
    }

    public function getJenisProjectLabelAttribute()
    {
        return match($this->jenis_project) {
            'cleaning_services' => 'Cleaning Services',
            'security_services' => 'Security Services',
            default => $this->jenis_project,
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }
}
