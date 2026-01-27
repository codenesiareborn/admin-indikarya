<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskSubmissionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_submission_id' => $this->task_submission_id,
            'task_list_id' => $this->task_list_id,
            'task_name' => $this->task?->nama_task,
            'task_description' => $this->task?->deskripsi,
            'task_order' => $this->task?->urutan,
            'is_completed' => $this->is_completed,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
