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
     * @param string $checkInTime Format: H:i (e.g., "08:15")
     * @param string $scheduledTime Format: H:i (e.g., "08:00")
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
     * @param UploadedFile $file
     * @param string $type 'check_in' or 'check_out'
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
     * Validate if user is assigned to project
     *
     * @param int $userId
     * @param int $projectId
     * @return bool
     */
    public function validateUserProject(int $userId, int $projectId): bool
    {
        return \DB::table('employee_projects')
            ->where('user_id', $userId)
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Generate random string
     *
     * @param int $length
     * @return string
     */
    private function generateRandomString(int $length = 8): string
    {
        return Str::random($length);
    }

    /**
     * Delete photo from storage
     *
     * @param string $path
     * @return bool
     */
    public function deletePhoto(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
}
