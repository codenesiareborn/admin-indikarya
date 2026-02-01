<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'check_in' => $this->check_in ? (is_string($this->check_in) ? substr($this->check_in, 0, 5) : $this->check_in->format('H:i')) : null,
            'check_in_photo_url' => $this->check_in_photo 
                ? url('storage/' . $this->check_in_photo) 
                : null,
            'check_in_latitude' => $this->check_in_latitude 
                ? (float) $this->check_in_latitude 
                : null,
            'check_in_longitude' => $this->check_in_longitude 
                ? (float) $this->check_in_longitude 
                : null,
            'check_in_address' => $this->check_in_address,
            'check_out' => $this->check_out ? (is_string($this->check_out) ? substr($this->check_out, 0, 5) : $this->check_out->format('H:i')) : null,
            'check_out_photo_url' => $this->check_out_photo 
                ? url('storage/' . $this->check_out_photo) 
                : null,
            'check_out_latitude' => $this->check_out_latitude 
                ? (float) $this->check_out_latitude 
                : null,
            'check_out_longitude' => $this->check_out_longitude 
                ? (float) $this->check_out_longitude 
                : null,
            'check_out_address' => $this->check_out_address,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
