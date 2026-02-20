<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectShift extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'active_days',
        'is_auto_generated',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'active_days' => 'array',
        'is_auto_generated' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'shift_id');
    }

    public function getStartTimeFormattedAttribute(): string
    {
        return $this->start_time?->format('H:i') ?? '-';
    }

    public function getEndTimeFormattedAttribute(): string
    {
        return $this->end_time?->format('H:i') ?? '-';
    }

    public function getScheduleLabelAttribute(): string
    {
        return "{$this->start_time_formatted} - {$this->end_time_formatted}";
    }

    public function getActiveDaysLabelAttribute(): string
    {
        if (count($this->active_days ?? []) === 7) {
            return 'Everyday';
        }

        $dayLabels = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];

        $labels = array_map(fn($day) => $dayLabels[$day] ?? $day, $this->active_days ?? []);
        return implode(', ', $labels);
    }

    public function isActiveOnDay(string $day): bool
    {
        return in_array(strtolower($day), array_map('strtolower', $this->active_days ?? []));
    }
}
