<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Export
Route::controller(App\Http\Controllers\ReportController::class)->group(function () {
    Route::get('/reports/attendance/excel', 'attendanceExcel')->name('reports.attendance.excel');
    Route::get('/reports/attendance/pdf', 'attendancePdf')->name('reports.attendance.pdf');
    Route::get('/reports/tasklist/excel', 'tasklistExcel')->name('reports.tasklist.excel');
    Route::get('/reports/tasklist/pdf', 'tasklistPdf')->name('reports.tasklist.pdf');
    Route::get('/reports/patrol/excel', 'patrolExcel')->name('reports.patrol.excel');
    Route::get('/reports/patrol/pdf', 'patrolPdf')->name('reports.patrol.pdf');
    Route::get('/reports/shift/excel', 'shiftExcel')->name('reports.shift.excel');
    Route::get('/reports/shift/pdf', 'shiftPdf')->name('reports.shift.pdf');
});
