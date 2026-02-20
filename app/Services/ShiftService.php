<?php

namespace App\Services;

use App\Models\ProjectShift;
use Carbon\Carbon;

class ShiftService
{
    /**
     * Get active shifts for a project on a specific day
     *
     * @param int $projectId
     * @param string|null $day Day name in English (lowercase) or null for today
     * @return array
     */
    public function getActiveShifts(int $projectId, ?string $day = null): \Illuminate\Database\Eloquent\Collection
    {
        $day = $day ?? strtolower(now()->format('l'));

        return ProjectShift::where('project_id', $projectId)
            ->where('is_active', true)
            ->whereJsonContains('active_days', $day)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Validate if shift is available on a specific day
     *
     * @param int $shiftId
     * @param string|null $day Day name in English (lowercase) or null for today
     * @return bool
     */
    public function validateShiftForDay(int $shiftId, ?string $day = null): bool
    {
        $day = $day ?? strtolower(now()->format('l'));

        return ProjectShift::where('id', $shiftId)
            ->where('is_active', true)
            ->whereJsonContains('active_days', $day)
            ->exists();
    }

    /**
     * Calculate attendance status based on check-in time and shift
     *
     * @param string $checkInTime Format: H:i (e.g., "08:15")
     * @param int $shiftId
     * @return string 'hadir' or 'terlambat'
     */
    public function calculateStatusWithShift(string $checkInTime, int $shiftId): string
    {
        $shift = ProjectShift::find($shiftId);

        if (!$shift) {
            return 'hadir';
        }

        try {
            $checkIn = Carbon::createFromFormat('H:i', $checkInTime);
            $scheduled = Carbon::createFromFormat('H:i', $shift->start_time->format('H:i'));

            // Grace period 5 minutes
            $scheduled->addMinutes(5);

            return $checkIn->lte($scheduled) ? 'hadir' : 'terlambat';
        } catch (\Exception $e) {
            // If parsing fails, default to 'hadir'
            return 'hadir';
        }
    }

    /**
     * Get shift details by ID
     *
     * @param int $shiftId
     * @return ProjectShift|null
     */
    public function getShiftById(int $shiftId): ?ProjectShift
    {
        return ProjectShift::find($shiftId);
    }

    /**
     * Get all shifts for a project
     *
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProjectShifts(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectShift::where('project_id', $projectId)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check if shift belongs to project
     *
     * @param int $shiftId
     * @param int $projectId
     * @return bool
     */
    public function isShiftInProject(int $shiftId, int $projectId): bool
    {
        return ProjectShift::where('id', $shiftId)
            ->where('project_id', $projectId)
            ->exists();
    }
}
