<?php

namespace App\Services;

use App\Models\ProjectRoom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckpointService
{
    /**
     * Upload checkpoint photo
     */
    public function uploadPhoto(UploadedFile $photo): string
    {
        $filename = time().'_'.uniqid().'.'.$photo->getClientOriginalExtension();
        $path = $photo->storeAs('checkpoints', $filename, 'public');

        return $path;
    }

    /**
     * Delete checkpoint photo
     */
    public function deletePhoto(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Validate user has access to project and room
     *
     * @throws \Exception
     */
    public function validateUserAccess(int $userId, int $projectId, int $roomId): void
    {
        // Check if user has active assignment to this project
        // Active assignment = project status 'aktif' AND (tanggal_selesai IS NULL OR tanggal_selesai >= today)
        $hasActiveAssignment = DB::table('employee_projects')
            ->join('projects', 'employee_projects.project_id', '=', 'projects.id')
            ->where('employee_projects.user_id', $userId)
            ->where('employee_projects.project_id', $projectId)
            ->where('projects.status', 'aktif')
            ->where(function ($query) {
                $query->whereNull('employee_projects.tanggal_selesai')
                    ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
            })
            ->exists();

        if (! $hasActiveAssignment) {
            throw new \Exception('Anda tidak memiliki akses ke project ini atau project sudah kadaluarsa.');
        }

        // Check if room belongs to project
        $room = ProjectRoom::where('id', $roomId)
            ->where('project_id', $projectId)
            ->first();

        if (! $room) {
            throw new \Exception('Ruangan tidak ditemukan dalam project ini.');
        }

        if ($room->status !== 'aktif') {
            throw new \Exception('Ruangan tidak aktif.');
        }
    }

    /**
     * Validate user has access to project
     *
     * @throws \Exception
     */
    public function validateUserProjectAccess(int $userId, int $projectId): void
    {
        // Check if user has active assignment to this project
        // Active assignment = project status 'aktif' AND (tanggal_selesai IS NULL OR tanggal_selesai >= today)
        $hasActiveAssignment = DB::table('employee_projects')
            ->join('projects', 'employee_projects.project_id', '=', 'projects.id')
            ->where('employee_projects.user_id', $userId)
            ->where('employee_projects.project_id', $projectId)
            ->where('projects.status', 'aktif')
            ->where(function ($query) {
                $query->whereNull('employee_projects.tanggal_selesai')
                    ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
            })
            ->exists();

        if (! $hasActiveAssignment) {
            throw new \Exception('Anda tidak memiliki akses ke project ini atau project sudah kadaluarsa.');
        }
    }

    /**
     * Calculate distance between two GPS coordinates (in meters)
     * Using Haversine formula
     *
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validate if coordinates are within allowed radius of project location
     * Note: This is a placeholder. You'll need to add location fields to projects table
     *
     * @param  float  $allowedRadius  (in meters, default 100m)
     */
    public function validateLocationRadius(
        float $userLat,
        float $userLon,
        float $projectLat,
        float $projectLon,
        float $allowedRadius = 100
    ): bool {
        $distance = $this->calculateDistance($userLat, $userLon, $projectLat, $projectLon);

        return $distance <= $allowedRadius;
    }

    /**
     * Get full URL for checkpoint photo
     */
    public function getPhotoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
