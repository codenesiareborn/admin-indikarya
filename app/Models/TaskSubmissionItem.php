<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmissionItem extends Model
{
    protected $fillable = [
        'task_submission_id',
        'task_list_id',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(TaskSubmission::class, 'task_submission_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'task_list_id');
    }
}
