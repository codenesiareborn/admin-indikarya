<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\TaskListExport;
use App\Models\Attendance;
use App\Models\GeneralSetting;
use App\Models\Patrol;
use App\Models\ShiftReport;
use App\Exports\ShiftReportExport;
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
            'active_employees' => $submissions->pluck('user_id')->unique()->count(),
        ];
    }
    /**
     * Download Patrol Report as Excel
     */
    public function patrolExcel(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $status = $request->get('status');
        $projectType = $request->get('project_type');

        $data = Patrol::query()
            ->with(['user', 'project', 'patrolArea'])
            ->when($startDate, fn ($q) => $q->whereDate('patrol_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('patrol_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getPatrolStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-PTR-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-patroli-{$startDate}-{$endDate}.xlsx";
        $path = "exports/{$filename}";
        
        // Save to storage first
        Excel::store(
            new \App\Exports\PatrolExport($data, $stats, $settings, $startDate, $endDate, $reportNumber),
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
     * Download Patrol Report as PDF
     */
    public function patrolPdf(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');
        $status = $request->get('status');
        $projectType = $request->get('project_type');

        $data = Patrol::query()
            ->with(['user', 'project', 'patrolArea'])
            ->when($startDate, fn ($q) => $q->whereDate('patrol_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('patrol_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($projectType, fn ($q) => $q->whereHas('project', fn ($q) => $q->where('jenis_project', $projectType)))
            ->get();

        $stats = $this->getPatrolStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-PTR-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-patroli-{$startDate}-{$endDate}.pdf";
        $fullPath = storage_path("app/public/exports/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf = Pdf::loadView('reports.patrol-report', [
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
     * Calculate patrol statistics
     */
    private function getPatrolStats($data)
    {
        $total = $data->count();
        $aman = $data->where('status', 'Aman')->count();
        $tidakAman = $data->where('status', 'Tidak Aman')->count();
        $presentase = $total > 0 ? round(($aman / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'aman' => $aman,
            'tidak_aman' => $tidakAman,
            'presentase' => $presentase,
            'active_officers' => $data->pluck('user_id')->unique()->count(),
        ];
    }
    /**
     * Download Shift Report as Excel
     */
    public function shiftExcel(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');

        $data = ShiftReport::query()
            ->with(['user', 'project'])
            ->when($startDate, fn ($q) => $q->whereDate('shift_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('shift_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->get();

        $stats = $this->getShiftStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-SFT-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-shift-{$startDate}-{$endDate}.xlsx";
        $path = "exports/{$filename}";
        
        Excel::store(
            new ShiftReportExport($data, $stats, $settings, $startDate, $endDate, $reportNumber),
            $path,
            'public'
        );

        $fullPath = storage_path("app/public/{$path}");

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Shift Report as PDF
     */
    public function shiftPdf(Request $request): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $projectId = $request->get('project_id');

        $data = ShiftReport::query()
            ->with(['user', 'project'])
            ->when($startDate, fn ($q) => $q->whereDate('shift_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('shift_date', '<=', $endDate))
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->get();

        $stats = $this->getShiftStats($data);
        $settings = GeneralSetting::getAllSettings();
        $reportNumber = 'LAP-SFT-' . now()->format('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $filename = "laporan-shift-{$startDate}-{$endDate}.pdf";
        $fullPath = storage_path("app/public/exports/{$filename}");

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf = Pdf::loadView('reports.shift-report', [
            'data' => $data,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportNumber' => $reportNumber,
        ])->setPaper('a4', 'landscape');

        $pdf->save($fullPath);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }

    private function getShiftStats($data)
    {
        return [
            'total' => $data->count(),
            'active_officers' => $data->pluck('user_id')->unique()->count(),
        ];
    }
}
