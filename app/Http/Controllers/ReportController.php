<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\TaskListExport;
use App\Models\Attendance;
use App\Models\GeneralSetting;
use App\Models\TaskSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Download Attendance Report as Excel
     */
    public function attendanceExcel(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $status = $request->get('status');
        $projectType = $request->get('project_type');

        $data = Attendance::query()
            ->with(['employee', 'project'])
            ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getAttendanceStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-ABS-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-presensi-{$startDate}-{$endDate}.xlsx";
        $path = "exports/{$filename}";
        
        // Save to storage first
        Excel::store(
            new AttendanceExport($data, $stats, $settings, $startDate, $endDate, $reportNumber),
            $path,
            'public'
        );

        $fullPath = storage_path("app/public/{$path}");

        // Return with explicit headers
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Attendance Report as PDF
     */
    public function attendancePdf(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $status = $request->get('status');
        $projectType = $request->get('project_type');

        $data = Attendance::query()
            ->with(['employee', 'project'])
            ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getAttendanceStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-ABS-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-presensi-{$startDate}-{$endDate}.pdf";
        $fullPath = storage_path("app/public/exports/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf = Pdf::loadView('reports.attendance-report', [
            'data' => $data,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportNumber' => $reportNumber,
        ])->setPaper('a4', 'landscape');

        // Save to file first
        $pdf->save($fullPath);

        // Return with explicit headers
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Task List Report as Excel
     */
    public function tasklistExcel(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $roomId = $request->get('room_id');
        $projectType = $request->get('project_type');

        $data = TaskSubmission::query()
            ->with(['employee', 'project', 'room', 'items'])
            ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($roomId, fn ($q) => $q->where('project_room_id', $roomId))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getTaskListStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-TSK-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-tasklist-{$startDate}-{$endDate}.xlsx";
        $path = "exports/{$filename}";

        // Save to storage first
        Excel::store(
            new TaskListExport($data, $stats, $settings, $startDate, $endDate, $reportNumber),
            $path,
            'public'
        );

        $fullPath = storage_path("app/public/{$path}");

        // Return with explicit headers
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Task List Report as PDF
     */
    public function tasklistPdf(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $roomId = $request->get('room_id');
        $projectType = $request->get('project_type');

        $data = TaskSubmission::query()
            ->with(['employee', 'project', 'room', 'items'])
            ->when($startDate, fn ($q) => $q->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($roomId, fn ($q) => $q->where('project_room_id', $roomId))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getTaskListStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-TSK-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-tasklist-{$startDate}-{$endDate}.pdf";
        $fullPath = storage_path("app/public/exports/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf = Pdf::loadView('reports.tasklist-report', [
            'data' => $data,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportNumber' => $reportNumber,
        ])->setPaper('a4', 'landscape');

        // Save to file first
        $pdf->save($fullPath);

        // Return with explicit headers
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Calculate attendance statistics
     */
    private function getAttendanceStats($data)
    {
        return [
            'total' => $data->count(),
            'hadir' => $data->where('status', 'hadir')->count(),
            'terlambat' => $data->where('status', 'terlambat')->count(),
            'izin' => $data->where('status', 'izin')->count(),
            'sakit' => $data->where('status', 'sakit')->count(),
            'alpha' => $data->where('status', 'alpha')->count(),
        ];
    }

    /**
     * Calculate task list statistics
     */
    private function getTaskListStats($submissions)
    {
        $totalCompleted = $submissions->sum(fn ($s) => $s->items->where('is_completed', true)->count());
        $totalPending = $submissions->sum(fn ($s) => $s->items->where('is_completed', false)->count());
        $totalTasks = $totalCompleted + $totalPending;
        $completionRate = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100, 1) : 0;

        return [
            'total_submissions' => $submissions->count(),
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
            'completion_rate' => $completionRate,
            'active_employees' => $submissions->pluck('employee_id')->unique()->count(),
        ];
    }
}
