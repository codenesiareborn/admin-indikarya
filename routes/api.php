<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CheckpointController;
use App\Http\Controllers\Api\PatrolController;
use App\Http\Controllers\Api\ShiftReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'profile']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // User projects
    Route::get('/user/projects', [ProjectController::class, 'index']);
    
    // Attendance endpoints
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendances/today', [AttendanceController::class, 'today']);
    Route::get('/attendances/history', [AttendanceController::class, 'history']);
    
    // Checkpoint endpoints
    Route::post('/checkpoints', [CheckpointController::class, 'submit']);
    Route::get('/checkpoints/today', [CheckpointController::class, 'today']);
    Route::get('/checkpoints/today-summary', [CheckpointController::class, 'todaySummary']);
    Route::get('/checkpoints/history', [CheckpointController::class, 'history']);
    Route::get('/checkpoints/{id}', [CheckpointController::class, 'show']);
    Route::get('/projects/{projectId}/rooms', [CheckpointController::class, 'getRooms']);
    Route::get('/rooms/{roomId}', [CheckpointController::class, 'getRoomDetail']);
    
    // Patrol endpoints
    Route::post('/patrols', [PatrolController::class, 'submit']);
    Route::get('/patrols/areas', [PatrolController::class, 'getAreas']);
    Route::get('/patrols/today', [PatrolController::class, 'today']);
    Route::get('/patrols/history', [PatrolController::class, 'history']);
    Route::get('/patrols/{id}', [PatrolController::class, 'show']);
    
    // Shift Report endpoints
    Route::post('/shift-reports', [ShiftReportController::class, 'submit']);
    Route::get('/shift-reports/today', [ShiftReportController::class, 'today']);
    Route::get('/shift-reports/history', [ShiftReportController::class, 'history']);
    Route::get('/shift-reports/{id}', [ShiftReportController::class, 'show']);
    Route::delete('/shift-reports/{id}', [ShiftReportController::class, 'destroy']);
});
