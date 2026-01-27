<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftReportResource extends JsonResource
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
            'report' => $this->report,
            'shift_date' => $this->shift_date->format('Y-m-d'),
            'shift_time' => $this->shift_time,
            'submitted_at' => $this->submitted_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
            ],
            'project' => $this->when($this->project, [
                'id' => $this->project->id ?? null,
                'name' => $this->project->nama_proyek ?? null,
            ]),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
