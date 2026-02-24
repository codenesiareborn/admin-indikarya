<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitCheckpointRequest;
use App\Http\Resources\CheckpointResource;
use App\Http\Resources\ProjectRoomResource;
use App\Models\ProjectRoom;
use App\Models\TaskSubmission;
use App\Models\TaskSubmissionItem;
use App\Services\CheckpointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckpointController extends Controller
{
    protected CheckpointService $checkpointService;

    public function __construct(CheckpointService $checkpointService)
    {
        $this->checkpointService = $checkpointService;
    }

    /**
     * Submit a checkpoint with photo, GPS, and checklist
     * 
     * @param SubmitCheckpointRequest $request
     * @return JsonResponse
     */
    public function submit(SubmitCheckpointRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            // Validate user has access to this project and room
            $this->checkpointService->validateUserAccess(
                $user->id,
                $request->project_id,
                $request->project_room_id
            );

            // Upload photo
            $photoPath = $this->checkpointService->uploadPhoto($request->file('photo'));

            // Create or update checkpoint (task submission)
            $checkpoint = TaskSubmission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'project_room_id' => $request->project_room_id,
                    'tanggal' => now()->toDateString(),
                ],
                [
                    'project_id' => $request->project_id,
                    'submitted_at' => now(),
                    'foto' => $photoPath,
                    'catatan' => $request->catatan,
                    'keterangan' => $request->keterangan,
                ]
            );

            // Save checkpoint items (checklist)
            if ($request->has('tasks')) {
                // Delete existing items for today
                $checkpoint->items()->delete();

                // Create new items
                foreach ($request->tasks as $taskData) {
                    TaskSubmissionItem::create([
                        'task_submission_id' => $checkpoint->id,
                        'task_list_id' => $taskData['task_list_id'],
                        'is_completed' => $taskData['is_completed'] ?? false,
                    ]);
                }
            }

            DB::commit();

            // Reload relationships
            $checkpoint->load(['room', 'project', 'items.task']);

            return response()->json([
                'success' => true,
                'message' => 'Checkpoint berhasil disimpan',
                'data' => new CheckpointResource($checkpoint),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if transaction fails
            if (isset($photoPath)) {
                $this->checkpointService->deletePhoto($photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's checkpoints for the authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function today(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $checkpoints = TaskSubmission::with(['room', 'project', 'items.task'])
            ->where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CheckpointResource::collection($checkpoints),
        ]);
    }

    /**
     * Get checkpoint history with pagination
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->input('per_page', 15);

        $checkpoints = TaskSubmission::with(['room', 'project', 'items.task'])
            ->where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CheckpointResource::collection($checkpoints),
            'pagination' => [
                'current_page' => $checkpoints->currentPage(),
                'last_page' => $checkpoints->lastPage(),
                'per_page' => $checkpoints->perPage(),
                'total' => $checkpoints->total(),
            ],
        ]);
    }

    /**
     * Get all rooms for a project
     * 
     * @param Request $request
     * @param int $projectId
     * @return JsonResponse
     */
    public function getRooms(Request $request, int $projectId): JsonResponse
    {
        $user = auth()->user();

        // Validate user has access to this project
        $this->checkpointService->validateUserProjectAccess($user->id, $projectId);

        $rooms = ProjectRoom::with(['tasks' => function ($query) {
                $query->where('status', 'aktif')->orderBy('urutan');
            }])
            ->where('project_id', $projectId)
            ->where('status', 'aktif')
            ->orderBy('lantai')
            ->orderBy('nama_ruangan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProjectRoomResource::collection($rooms),
        ]);
    }

    /**
     * Get room detail with tasks
     * 
     * @param int $roomId
     * @return JsonResponse
     */
    public function getRoomDetail(int $roomId): JsonResponse
    {
        $user = auth()->user();

        $room = ProjectRoom::with(['tasks' => function ($query) {
                $query->where('status', 'aktif')->orderBy('urutan');
            }, 'project'])
            ->findOrFail($roomId);

        // Validate user has access to this room's project
        $this->checkpointService->validateUserProjectAccess($user->id, $room->project_id);

        return response()->json([
            'success' => true,
            'data' => new ProjectRoomResource($room),
        ]);
    }

    /**
     * Get checkpoint summary for today (count by room)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function todaySummary(Request $request): JsonResponse
    {
        $user = auth()->user();
        $projectId = $request->input('project_id');

        $query = TaskSubmission::with(['room'])
            ->where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString());

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $checkpoints = $query->get();

        $summary = [
            'total_checkpoints' => $checkpoints->count(),
            'rooms_completed' => $checkpoints->pluck('project_room_id')->unique()->count(),
            'checkpoints' => $checkpoints->map(function ($checkpoint) {
                return [
                    'id' => $checkpoint->id,
                    'room_id' => $checkpoint->project_room_id,
                    'room_name' => $checkpoint->room->nama_ruangan,
                    'time' => $checkpoint->submitted_at->format('H:i:s'),
                    'completed_tasks' => $checkpoint->completed_count,
                    'total_tasks' => $checkpoint->total_tasks,
                    'completion_rate' => $checkpoint->completion_rate,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get checkpoint detail
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $checkpoint = TaskSubmission::with(['room', 'project', 'items.task'])
            ->findOrFail($id);

        // Security check: Ensure user owns this checkpoint
        if ($checkpoint->user_id !== $user->id) {
             return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new CheckpointResource($checkpoint),
        ]);
    }
}
