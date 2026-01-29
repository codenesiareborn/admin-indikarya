<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\ShiftReport;
use Illuminate\Database\Seeder;

class ShiftReportSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $employees = User::where('status_pegawai', 'aktif')->get();

        if ($projects->isEmpty() || $employees->isEmpty()) {
            $this->command->info('No projects or employees found. Skipping ShiftReportSeeder.');
            return;
        }

        $reportTemplates = [
            'Shift berjalan dengan lancar, tidak ada kejadian khusus.',
            'Semua area sudah diperiksa dan dalam kondisi baik.',
            'Tamu/pengunjung sudah diverifikasi sesuai prosedur.',
            'AC dan lampu sudah diperiksa dan berfungsi normal.',
            'Pintu dan jendela sudah dikunci dengan aman.',
            'Tidak ada temuan yang perlu dilaporkan.',
            'Peralatan safety sudah diperiksa dan dalam kondisi baik.',
            'Area parkir dalam kondisi aman dan tertib.',
        ];

        // Generate shift reports for the last 14 days
        foreach (range(0, 13) as $daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            
            foreach ($projects as $project) {
                // Random 1-3 employees submit shift reports per project per day
                $randomEmployees = $employees->random(min(rand(1, 3), $employees->count()));
                
                foreach ($randomEmployees as $employee) {
                    // Check if report already exists
                    $existingReport = ShiftReport::where('user_id', $employee->id)
                        ->where('project_id', $project->id)
                        ->whereDate('shift_date', $date)
                        ->exists();

                    if ($existingReport) continue;

                    // Random shift time (morning: 06-08, afternoon: 14-16, night: 22-00)
                    $shiftHours = [6, 7, 8, 14, 15, 16, 22, 23];
                    $hour = $shiftHours[array_rand($shiftHours)];
                    $shiftTime = sprintf('%02d:%02d:00', $hour, rand(0, 59));

                    $submittedHour = $hour + rand(0, 2); // Submitted within 0-2 hours after shift start
                    $submittedAt = $date . ' ' . sprintf('%02d:%02d:%02d', min($submittedHour, 23), rand(0, 59), rand(0, 59));

                    ShiftReport::create([
                        'user_id' => $employee->id,
                        'project_id' => $project->id,
                        'report' => $reportTemplates[array_rand($reportTemplates)],
                        'shift_date' => $date,
                        'shift_time' => $shiftTime,
                        'submitted_at' => $submittedAt,
                    ]);
                }
            }
        }

        $this->command->info('✅ Shift Report seeder completed!');
    }
}
