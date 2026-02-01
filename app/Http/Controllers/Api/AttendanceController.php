<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckInRequest;
use App\Http\Requests\Api\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Project;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
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
            $today = now()->toDateString();

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

            // Get project to get schedule
            $project = Project::findOrFail($projectId);

            // Upload photo
            $photoPath = $this->attendanceService->uploadPhoto(
                $request->file('photo'),
                'check_in'
            );

            // Get current time
            $checkInTime = now()->format('H:i');

            // Get project schedule times for snapshot
            $jamMasukSnapshot = $project->jam_masuk?->format('H:i');
            $jamPulangSnapshot = $project->jam_pulang?->format('H:i');

            // Calculate status using the current project schedule
            $status = $this->attendanceService->calculateStatus(
                $checkInTime,
                $jamMasukSnapshot
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
                    'jam_masuk_snapshot' => $jamMasukSnapshot,
                    'jam_pulang_snapshot' => $jamPulangSnapshot,
                    'status' => $status,
                ]);
                $attendance = $existingAttendance;
            } else {
                // Create new attendance record with schedule snapshots
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'tanggal' => $today,
                    'check_in' => $checkInTime,
                    'check_in_photo' => $photoPath,
                    'check_in_latitude' => $validated['latitude'],
                    'check_in_longitude' => $validated['longitude'],
                    'check_in_address' => $validated['address'] ?? null,
                    'jam_masuk_snapshot' => $jamMasukSnapshot,
                    'jam_pulang_snapshot' => $jamPulangSnapshot,
                    'status' => $status,
                ]);
            }

            // Load project relation
            $attendance->load('project');

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'data' => new AttendanceResource($attendance),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-in: ' . $e->getMessage(),
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
            $today = now()->toDateString();

            // Check if already checked in today
            $attendance = Attendance::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->whereDate('tanggal', $today)
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in hari ini',
                ], 400);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out hari ini',
                ], 409);
            }

            // Upload photo
            $photoPath = $this->attendanceService->uploadPhoto(
                $request->file('photo'),
                'check_out'
            );

            // Get current time
            $checkOutTime = now()->format('H:i');

            // Ensure schedule snapshots are set (for backward compatibility with existing records)
            $updateData = [
                'check_out' => $checkOutTime,
                'check_out_photo' => $photoPath,
                'check_out_latitude' => $validated['latitude'],
                'check_out_longitude' => $validated['longitude'],
                'check_out_address' => $validated['address'] ?? null,
            ];

            // If schedule snapshots are not set, use current project schedule
            if (!$attendance->jam_masuk_snapshot || !$attendance->jam_pulang_snapshot) {
                $project = Project::findOrFail($projectId);
                $updateData['jam_masuk_snapshot'] = $attendance->jam_masuk_snapshot ?? $project->jam_masuk?->format('H:i');
                $updateData['jam_pulang_snapshot'] = $attendance->jam_pulang_snapshot ?? $project->jam_pulang?->format('H:i');
            }

            // Update attendance with check-out data
            $attendance->update($updateData);

            // Load project relation
            $attendance->load('project');

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => new AttendanceResource($attendance),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-out: ' . $e->getMessage(),
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

            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('tanggal', $today)
                ->with('project')
                ->first();

            if (!$attendance) {
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
                ->with('project')
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
