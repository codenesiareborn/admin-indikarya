<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'project_room_id',
        'tanggal',
        'submitted_at',
        'foto',
        'catatan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for backward compatibility
    public function employee(): BelongsTo
    {
        return $this->user();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TaskSubmissionItem::class);
    }

    public function getCompletedCountAttribute(): int
    {
        return $this->items()->where('is_completed', true)->count();
    }

    public function getTotalTasksAttribute(): int
    {
        return $this->items()->count();
    }

    public function getCompletionRateAttribute(): float
    {
        $total = $this->total_tasks;
        if ($total === 0) return 0;
        return round(($this->completed_count / $total) * 100, 1);
    }
}
