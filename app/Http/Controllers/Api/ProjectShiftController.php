<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectShiftResource;
use App\Models\Project;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectShiftController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Get active shifts for a project
     */
    public function getProjectShifts(int $projectId): JsonResponse
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                ], 404);
            }

            // Get current day
            $currentDay = strtolower(now()->format('l'));
            
            // Get active shifts for today
            $shifts = $this->shiftService->getActiveShifts($projectId, $currentDay);

            return response()->json([
                'success' => true,
                'data' => ProjectShiftResource::collection($shifts),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching shifts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all shifts for a project (including inactive)
     */
    public function getAllProjectShifts(int $projectId): JsonResponse
    {
        try {
            $project = Project::find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                ], 404);
            }

            $shifts = $this->shiftService->getProjectShifts($projectId);

            return response()->json([
                'success' => true,
                'data' => ProjectShiftResource::collection($shifts),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching shifts: ' . $e->getMessage(),
            ], 500);
        }
    }
}
