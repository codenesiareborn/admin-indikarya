<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Attendance;
use App\Models\ProjectShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoMarkAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-mark-alpha {--date= : Specific date to process (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark alpha for employees who did not check in';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processDate = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : now()->setTimezone('Asia/Jakarta');

        $this->info("Processing auto mark alpha for date: {$processDate->format('Y-m-d')}");

        $projects = Project::where('auto_mark_alpha', true)
            ->where('status', 'aktif')
            ->with(['employees', 'shifts'])
            ->get();

        if ($projects->isEmpty()) {
            $this->info('No active projects with auto mark alpha enabled.');
            return 0;
        }

        $totalMarked = 0;

        DB::beginTransaction();
        try {
            foreach ($projects as $project) {
                $this->info("Processing project: {$project->nama_project}");

                $employees = $project->employees()
                    ->where('role', 'employee')
                    ->get();

                foreach ($employees as $employee) {
                    if ($this->shouldMarkAlpha($employee, $project, $processDate)) {
                        $this->createAlphaAttendance($employee, $project, $processDate);
                        $totalMarked++;
                        $this->line("  - Marked alpha: {$employee->name}");
                    }
                }
            }

            DB::commit();
            $this->info("Successfully marked {$totalMarked} employees as alpha.");
            
            Log::info('Auto mark alpha completed', [
                'date' => $processDate->format('Y-m-d'),
                'total_marked' => $totalMarked,
            ]);

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            
            Log::error('Auto mark alpha failed', [
                'date' => $processDate->format('Y-m-d'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Check if employee should be marked as alpha
     */
    private function shouldMarkAlpha($employee, $project, $processDate): bool
    {
        $alreadyHasAttendance = Attendance::where('user_id', $employee->id)
            ->where('project_id', $project->id)
            ->whereDate('tanggal', $processDate->format('Y-m-d'))
            ->exists();

        if ($alreadyHasAttendance) {
            return false;
        }

        return $this->isWorkingDay($project, $processDate);
    }

    /**
     * Check if the date is a working day for the project
     */
    private function isWorkingDay($project, $processDate): bool
    {
        $shifts = $project->shifts;

        if ($shifts->isEmpty()) {
            return true;
        }

        $dayName = strtolower($processDate->format('l'));
        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        $indonesianDay = $dayMapping[$dayName] ?? $dayName;

        foreach ($shifts as $shift) {
            if ($shift->active_days && is_array($shift->active_days)) {
                if (in_array($indonesianDay, $shift->active_days)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create alpha attendance record
     */
    private function createAlphaAttendance($employee, $project, $processDate): void
    {
        Attendance::create([
            'user_id' => $employee->id,
            'project_id' => $project->id,
            'tanggal' => $processDate->format('Y-m-d'),
            'status' => 'alpha',
            'check_in' => null,
            'check_out' => null,
            'keterangan' => 'Auto-generated: Tidak hadir',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
