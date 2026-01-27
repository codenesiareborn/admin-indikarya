<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftReport extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'report',
        'shift_date',
        'shift_time',
        'submitted_at',
    ];

    protected $casts = [
        'shift_date' => 'date',
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
}
