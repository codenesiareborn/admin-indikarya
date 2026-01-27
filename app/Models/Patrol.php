<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patrol extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'patrol_area_id',
        'area_name',
        'area_code',
        'status',
        'note',
        'photo',
        'patrol_date',
        'patrol_time',
        'submitted_at',
    ];

    protected $casts = [
        'patrol_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function patrolArea(): BelongsTo
    {
        return $this->belongsTo(PatrolArea::class);
    }
}
