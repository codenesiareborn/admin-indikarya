<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Report Download Routes (bypass Livewire)
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/attendance/excel', [ReportController::class, 'attendanceExcel'])->name('attendance.excel');
    Route::get('/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('attendance.pdf');
    Route::get('/tasklist/excel', [ReportController::class, 'tasklistExcel'])->name('tasklist.excel');
    Route::get('/tasklist/pdf', [ReportController::class, 'tasklistPdf'])->name('tasklist.pdf');
});
