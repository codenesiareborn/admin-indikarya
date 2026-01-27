<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftReportResource;
use App\Models\ShiftReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftReportController extends Controller
{
    /**
     * Submit a new shift report
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'report' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            // Create shift report record
            $shiftReport = ShiftReport::create([
                'user_id' => $user->id,
                'project_id' => $request->project_id,
                'report' => $request->report,
                'shift_date' => now()->toDateString(),
                'shift_time' => now()->toTimeString(),
                'submitted_at' => now(),
            ]);

            DB::commit();

            $shiftReport->load(['user', 'project']);

            return response()->json([
                'success' => true,
                'message' => 'Laporan shift berhasil disimpan',
                'data' => new ShiftReportResource($shiftReport),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan laporan shift: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's shift reports for the authenticated user
     */
    public function today(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $shiftReports = ShiftReport::with(['user', 'project'])
            ->where('user_id', $user->id)
            ->whereDate('shift_date', now()->toDateString())
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ShiftReportResource::collection($shiftReports),
        ]);
    }

    /**
     * Get shift report history with pagination
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->input('per_page', 15);

        $shiftReports = ShiftReport::with(['user', 'project'])
            ->where('user_id', $user->id)
            ->orderBy('shift_date', 'desc')
            ->orderBy('shift_time', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ShiftReportResource::collection($shiftReports),
            'pagination' => [
                'current_page' => $shiftReports->currentPage(),
                'last_page' => $shiftReports->lastPage(),
                'per_page' => $shiftReports->perPage(),
                'total' => $shiftReports->total(),
            ],
        ]);
    }

    /**
     * Get shift report detail by ID
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $shiftReport = ShiftReport::with(['user', 'project'])
            ->findOrFail($id);

        if ($shiftReport->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new ShiftReportResource($shiftReport),
        ]);
    }

    /**
     * Delete a shift report
     */
    public function destroy(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $shiftReport = ShiftReport::findOrFail($id);

        if ($shiftReport->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $shiftReport->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan shift berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus laporan shift: ' . $e->getMessage(),
            ], 500);
        }
    }
}
