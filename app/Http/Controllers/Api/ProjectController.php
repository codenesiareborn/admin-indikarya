<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Get user's assigned projects
     * Returns only projects where user has active assignment (status 'aktif' AND not expired)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get projects assigned to this user with active assignment
        // Active assignment = project status 'aktif' AND (tanggal_selesai IS NULL OR tanggal_selesai >= today)
        $projects = $user->projects()
            ->with(['rooms.tasks'])
            ->where('projects.status', 'aktif')
            ->where(function ($query) {
                $query->whereNull('employee_projects.tanggal_selesai')
                    ->orWhere('employee_projects.tanggal_selesai', '>=', now()->toDateString());
            })
            ->orderBy('employee_projects.tanggal_mulai', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($projects),
        ], 200);
    }
}
