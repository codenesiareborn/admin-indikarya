<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceService
{
    /**
     * Calculate attendance status based on check-in time and scheduled time
     *
     * @param  string  $checkInTime  Format: H:i (e.g., "08:15")
     * @param  string  $scheduledTime  Format: H:i (e.g., "08:00")
     * @return string 'hadir' or 'terlambat'
     */
    public function calculateStatus(string $checkInTime, string $scheduledTime): string
    {
        try {
            $checkIn = Carbon::createFromFormat('H:i', $checkInTime);
            $scheduled = Carbon::createFromFormat('H:i', $scheduledTime);

            // Grace period 5 minutes
            $scheduled->addMinutes(5);

            return $checkIn->lte($scheduled) ? 'hadir' : 'terlambat';
        } catch (\Exception $e) {
            // If parsing fails, default to 'hadir'
            return 'hadir';
        }
    }

    /**
     * Upload photo to storage
     *
     * @param  string  $type  'check_in' or 'check_out'
     * @return string Path to uploaded file
     */
    public function uploadPhoto(UploadedFile $file, string $type = 'check_in'): string
    {
        // Generate unique filename
        $date = now()->format('Y-m-d');
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();
        $filename = "{$type}_{$date}_{$timestamp}_{$this->generateRandomString(8)}.{$extension}";

        // Store in public disk under attendances directory
        $path = $file->storeAs('attendances', $filename, 'public');

        return $path;
    }

    /**
     * Validate if user has active assignment to project
     */
    public function validateUserProject(int $userId, int $projectId): bool
    {
        return \DB::table('employee_projects')
            ->join('projects', 'employee_projects.project_id', '=', 'projects.id')
            ->where('employee_projects.user_id', $userId)
            ->where('employee_projects.project_id', $projectId)
            ->where('projects.status', 'aktif')
            ->where(function ($query) {
                $query->whereNull('employee_projects.tanggal_selesai')
                    ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
            })
            ->exists();
    }

    /**
     * Generate random string
     */
    private function generateRandomString(int $length = 8): string
    {
        return Str::random($length);
    }

    /**
     * Delete photo from storage
     */
    public function deletePhoto(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
