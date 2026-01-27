<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatrolResource;
use App\Models\Patrol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PatrolController extends Controller
{
    /**
     * Submit a new patrol report
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'patrol_area_id' => 'nullable|exists:patrol_areas,id',
            'area_name' => 'required|string|max:255',
            'area_code' => 'required|string|max:50',
            'status' => 'required|in:Aman,Tidak Aman',
            'note' => 'nullable|string|max:1000',
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
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
            
            // Upload photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = 'patrol_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('patrols', $filename, 'public');
            }

            // Create patrol record
            $patrol = Patrol::create([
                'user_id' => $user->id,
                'project_id' => $request->project_id,
                'patrol_area_id' => $request->patrol_area_id,
                'area_name' => $request->area_name,
                'area_code' => $request->area_code,
                'status' => $request->status,
                'note' => $request->note,
                'photo' => $photoPath,
                'patrol_date' => now()->toDateString(),
                'patrol_time' => now()->toTimeString(),
                'submitted_at' => now(),
            ]);

            DB::commit();

            $patrol->load(['user', 'project', 'patrolArea']);

            return response()->json([
                'success' => true,
                'message' => 'Patrol berhasil disimpan',
                'data' => new PatrolResource($patrol),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan patrol: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's patrols for the authenticated user
     */
    public function today(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $patrols = Patrol::with(['user', 'project', 'patrolArea'])
            ->where('user_id', $user->id)
            ->whereDate('patrol_date', now()->toDateString())
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PatrolResource::collection($patrols),
        ]);
    }

    /**
     * Get patrol history with pagination
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->input('per_page', 15);

        $patrols = Patrol::with(['user', 'project', 'patrolArea'])
            ->where('user_id', $user->id)
            ->orderBy('patrol_date', 'desc')
            ->orderBy('patrol_time', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PatrolResource::collection($patrols),
            'pagination' => [
                'current_page' => $patrols->currentPage(),
                'last_page' => $patrols->lastPage(),
                'per_page' => $patrols->perPage(),
                'total' => $patrols->total(),
            ],
        ]);
    }

    /**
     * Get patrol detail by ID
     */
    public function show(int $id): JsonResponse
    {
        $user = auth()->user();
        
        $patrol = Patrol::with(['user', 'project', 'patrolArea'])
            ->findOrFail($id);

        if ($patrol->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PatrolResource($patrol),
        ]);
    }

    /**
     * Get available patrol areas for a project
     */
    public function getAreas(Request $request): JsonResponse
    {
        $user = auth()->user();
        $projectId = $request->input('project_id');

        // If no project_id provided, get from user's first project
        if (!$projectId) {
            $userProject = $user->projects()->first();
            if (!$userProject) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki project',
                ], 404);
            }
            $projectId = $userProject->id;
        }

        $areas = \App\Models\PatrolArea::where('project_id', $projectId)
            ->where('status', 'aktif')
            ->orderBy('urutan')
            ->orderBy('nama_area')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'kode_area' => $area->kode_area,
                    'name' => $area->nama_area,
                    'description' => $area->deskripsi,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }
}
