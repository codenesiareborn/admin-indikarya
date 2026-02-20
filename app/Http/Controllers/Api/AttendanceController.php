<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckInRequest;
use App\Http\Requests\Api\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Project;
use App\Models\ProjectShift;
use App\Services\AttendanceService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    protected ShiftService $shiftService;

    public function __construct(AttendanceService $attendanceService, ShiftService $shiftService)
    {
        $this->attendanceService = $attendanceService;
        $this->shiftService = $shiftService;
    }

    /**
     * Check-in attendance
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userId = auth()->id();
            $projectId = $validated['project_id'];
            $shiftId = $validated['shift_id'];
            $today = now()->toDateString();

            // Check if user has a pending overnight shift from yesterday
            $pendingOvernight = $this->shiftService->getPendingOvernightShift($userId, $projectId);
            if ($pendingOvernight) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda memiliki shift malam ('.$pendingOvernight['shift_name'].') yang belum check-out. Silakan check-out terlebih dahulu.',
                    'pending_shift' => $pendingOvernight,
                ], 409);
            }

            // Check if already checked in today
            $existingAttendance = Attendance::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->whereDate('tanggal', $today)
                ->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in hari ini',
                ], 409);
            }

            // Get shift details
            $shift = ProjectShift::findOrFail($shiftId);

            // Upload photo
            $photoPath = $this->attendanceService->uploadPhoto(
                $request->file('photo'),
                'check_in'
            );

            // Get current time
            $checkInTime = now()->format('H:i');

            // Get shift schedule times for snapshot
            $jamMasukSnapshot = $shift->start_time?->format('H:i');
            $jamPulangSnapshot = $shift->end_time?->format('H:i');

            // Calculate status using the shift schedule
            $status = $this->shiftService->calculateStatusWithShift(
                $checkInTime,
                $shiftId
            );

            // Create or update attendance
            if ($existingAttendance) {
                // Update existing record (if check_in is null)
                $existingAttendance->update([
                    'check_in' => $checkInTime,
                    'check_in_photo' => $photoPath,
                    'check_in_latitude' => $validated['latitude'],
                    'check_in_longitude' => $validated['longitude'],
                    'check_in_address' => $validated['address'] ?? null,
                    'shift_id' => $shiftId,
                    'jam_masuk_snapshot' => $jamMasukSnapshot,
                    'jam_pulang_snapshot' => $jamPulangSnapshot,
                    'shift_name_snapshot' => $shift->name,
                    'status' => $status,
                ]);
                $attendance = $existingAttendance;
            } else {
                // Create new attendance record with schedule snapshots
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'shift_id' => $shiftId,
                    'tanggal' => $today,
                    'check_in' => $checkInTime,
                    'check_in_photo' => $photoPath,
                    'check_in_latitude' => $validated['latitude'],
                    'check_in_longitude' => $validated['longitude'],
                    'check_in_address' => $validated['address'] ?? null,
                    'jam_masuk_snapshot' => $jamMasukSnapshot,
                    'jam_pulang_snapshot' => $jamPulangSnapshot,
                    'shift_name_snapshot' => $shift->name,
                    'status' => $status,
                ]);
            }

            // Load project and shift relations
            $attendance->load(['project', 'shift']);

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'data' => new AttendanceResource($attendance),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-in: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check-out attendance
     */
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $userId = auth()->id();
            $projectId = $validated['project_id'];
            $shiftId = $validated['shift_id'] ?? null;
            $today = now()->toDateString();

            // Get attendance record - supports overnight shifts
            $attendance = null;

            if ($shiftId) {
                // If shift_id is provided, use the service to find attendance (handles overnight)
                $attendance = $this->shiftService->getAttendanceForCheckout($userId, $projectId, $shiftId);
            } else {
                // Backward compatibility: search today's attendance only
                $attendance = Attendance::where('user_id', $userId)
                    ->where('project_id', $projectId)
                    ->whereDate('tanggal', $today)
                    ->first();
            }

            if (! $attendance || ! $attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in',
                ], 400);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out',
                ], 409);
            }

            // Validate check-out time is within allowed window
            $checkOutTime = now()->format('H:i');
            if (! $this->shiftService->isValidCheckoutTime($checkOutTime, $attendance->shift_id, $attendance->tanggal->toDateString())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu check-out melebihi batas toleransi yang diizinkan',
                ], 400);
            }

            // Upload photo
            $photoPath = $this->attendanceService->uploadPhoto(
                $request->file('photo'),
                'check_out'
            );

            // Ensure schedule snapshots are set (for backward compatibility with existing records)
            $updateData = [
                'check_out' => $checkOutTime,
                'check_out_photo' => $photoPath,
                'check_out_latitude' => $validated['latitude'],
                'check_out_longitude' => $validated['longitude'],
                'check_out_address' => $validated['address'] ?? null,
            ];

            // If schedule snapshots are not set, use shift schedule
            if (! $attendance->jam_masuk_snapshot || ! $attendance->jam_pulang_snapshot) {
                $shift = ProjectShift::find($attendance->shift_id);
                $updateData['jam_masuk_snapshot'] = $attendance->jam_masuk_snapshot ?? $shift?->start_time?->format('H:i');
                $updateData['jam_pulang_snapshot'] = $attendance->jam_pulang_snapshot ?? $shift?->end_time?->format('H:i');
            }

            // Update attendance with check-out data
            $attendance->update($updateData);

            // Load project and shift relations
            $attendance->load(['project', 'shift']);

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => new AttendanceResource($attendance),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-out: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $today = now()->toDateString();

            // Check today's attendance
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('tanggal', $today)
                ->with(['project', 'shift'])
                ->first();

            // If no attendance today, check for pending overnight shift from yesterday
            if (! $attendance) {
                $yesterday = now()->subDay()->toDateString();
                $pendingAttendance = Attendance::where('user_id', $userId)
                    ->whereDate('tanggal', $yesterday)
                    ->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->with(['project', 'shift'])
                    ->first();

                // Only return pending overnight shift if the shift is actually overnight
                if ($pendingAttendance && $pendingAttendance->shift?->is_overnight) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Anda memiliki shift malam yang belum check-out',
                        'data' => new AttendanceResource($pendingAttendance),
                        'is_overnight_pending' => true,
                    ], 200);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada presensi hari ini',
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => new AttendanceResource($attendance),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attendance history (paginated)
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $perPage = $request->input('per_page', 15);

            $attendances = Attendance::where('user_id', $userId)
                ->with(['project', 'shift'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => AttendanceResource::collection($attendances->items()),
                'pagination' => [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }
}
