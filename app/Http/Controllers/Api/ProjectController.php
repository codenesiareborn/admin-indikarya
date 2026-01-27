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
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get projects assigned to this user
        $projects = $user->projects()
            ->with(['rooms.tasks'])
            ->where('status', 'aktif')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($projects),
        ], 200);
    }
}
