<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ProjectShift;
use Carbon\Carbon;

class ShiftService
{
    /**
     * Get active shifts for a project on a specific day
     *
     * @param  string|null  $day  Day name in English (lowercase) or null for today
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
     * @param  string|null  $day  Day name in English (lowercase) or null for today
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
     * @param  string  $checkInTime  Format: H:i (e.g., "08:15")
     * @return string 'hadir' or 'terlambat'
     */
    public function calculateStatusWithShift(string $checkInTime, int $shiftId): string
    {
        $shift = ProjectShift::find($shiftId);

        if (! $shift) {
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
     */
    public function getShiftById(int $shiftId): ?ProjectShift
    {
        return ProjectShift::find($shiftId);
    }

    /**
     * Get all shifts for a project
     */
    public function getProjectShifts(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectShift::where('project_id', $projectId)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check if shift belongs to project
     */
    public function isShiftInProject(int $shiftId, int $projectId): bool
    {
        return ProjectShift::where('id', $shiftId)
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Check if shift is an overnight shift
     */
    public function isOvernightShift(int $shiftId): bool
    {
        $shift = ProjectShift::find($shiftId);

        return $shift?->is_overnight ?? false;
    }

    /**
     * Get attendance record for check-out
     * Searches today's record first, then yesterday's for overnight shifts
     */
    public function getAttendanceForCheckout(int $userId, int $projectId, int $shiftId): ?Attendance
    {
        $today = now()->toDateString();

        // First, try to find today's attendance
        $attendance = Attendance::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->where('shift_id', $shiftId)
            ->whereDate('tanggal', $today)
            ->first();

        if ($attendance) {
            return $attendance;
        }

        // If not found and shift is overnight, check yesterday's attendance
        if ($this->isOvernightShift($shiftId)) {
            $yesterday = now()->subDay()->toDateString();

            $attendance = Attendance::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->where('shift_id', $shiftId)
                ->whereDate('tanggal', $yesterday)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->first();

            return $attendance;
        }

        return null;
    }

    /**
     * Check if user has a pending overnight shift from yesterday that needs check-out
     *
     * @return array|null Returns array with shift info if pending, null otherwise
     */
    public function getPendingOvernightShift(int $userId, int $projectId): ?array
    {
        $yesterday = now()->subDay()->toDateString();

        $pendingAttendance = Attendance::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->whereDate('tanggal', $yesterday)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->with('shift')
            ->first();

        if ($pendingAttendance && $pendingAttendance->shift?->is_overnight) {
            return [
                'attendance_id' => $pendingAttendance->id,
                'shift_id' => $pendingAttendance->shift_id,
                'shift_name' => $pendingAttendance->shift->name,
                'tanggal' => $pendingAttendance->tanggal,
            ];
        }

        return null;
    }

    /**
     * Validate if check-out time is within allowed window for shift
     * For overnight shifts: allow up to 2 hours after end_time
     *
     * @param  string  $checkOutTime  Format: H:i
     * @param  string|null  $attendanceDate  Date of the attendance record
     */
    public function isValidCheckoutTime(string $checkOutTime, int $shiftId, ?string $attendanceDate = null): bool
    {
        $shift = ProjectShift::find($shiftId);

        if (! $shift) {
            return true;
        }

        try {
            $checkOut = Carbon::createFromFormat('H:i', $checkOutTime);
            $endTime = Carbon::createFromFormat('H:i', $shift->end_time->format('H:i'));

            // For overnight shifts, add 2 hours buffer to end_time
            if ($shift->is_overnight) {
                $endTime->addHours(2);

                // If attendance date is yesterday, we need to account for the day difference
                if ($attendanceDate && $attendanceDate !== now()->toDateString()) {
                    // Check-out is on the next day, so it's always valid if within buffer
                    return true;
                }

                // If checking out on the same day (rare case), ensure it's after start time
                $startTime = Carbon::createFromFormat('H:i', $shift->start_time->format('H:i'));
                if ($checkOut->lt($startTime)) {
                    return true; // Before start time means it's the next day
                }
            }

            return $checkOut->lte($endTime);
        } catch (\Exception $e) {
            return true;
        }
    }
}
