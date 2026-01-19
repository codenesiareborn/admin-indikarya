<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRoom extends Model
{
    protected $fillable = [
        'project_id',
        'nama_ruangan',
        'deskripsi',
        'lantai',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskList::class)->orderBy('urutan');
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
