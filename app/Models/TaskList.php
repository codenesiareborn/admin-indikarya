<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    protected $fillable = [
        'project_room_id',
        'nama_task',
        'deskripsi',
        'urutan',
        'status',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function submissionItems(): HasMany
    {
        return $this->hasMany(TaskSubmissionItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'aktif' => 'Aktif',
            'non-aktif' => 'Non-Aktif',
            default => $this->status,
        };
    }
}
