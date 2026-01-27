<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrolArea extends Model
{
    protected $fillable = [
        'project_id',
        'kode_area',
        'nama_area',
        'deskripsi',
        'status',
        'urutan',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'aktif' => 'Aktif',
            'non-aktif' => 'Non-Aktif',
            default => $this->status,
        };
    }
}
