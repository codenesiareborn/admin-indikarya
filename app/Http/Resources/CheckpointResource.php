<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckpointResource extends JsonResource
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
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->nama_project,
            'project_room_id' => $this->project_room_id,
            'room_name' => $this->room?->nama_ruangan,
            'room_floor' => $this->room?->lantai,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'submitted_time' => $this->submitted_at?->format('H:i:s'),
            'foto_url' => $this->foto ? url('storage/' . $this->foto) : null,
            'catatan' => $this->catatan,
            'tasks' => TaskSubmissionItemResource::collection($this->whenLoaded('items')),
            'completed_count' => $this->completed_count,
            'total_tasks' => $this->total_tasks,
            'completion_rate' => $this->completion_rate,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
