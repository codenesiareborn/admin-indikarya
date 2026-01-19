<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\TaskList;
use App\Models\TaskSubmission;
use App\Models\TaskSubmissionItem;
use Illuminate\Database\Seeder;

class TaskSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::with(['rooms.tasks'])->get();
        $employees = Employee::where('status_pegawai', 'aktif')->get();

        if ($projects->isEmpty() || $employees->isEmpty()) {
            $this->command->info('No projects or employees found. Skipping TaskSubmissionSeeder.');
            return;
        }

        // Generate submissions for the last 7 days
        foreach (range(0, 6) as $daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            
            foreach ($projects as $project) {
                foreach ($project->rooms as $room) {
                    if ($room->tasks->isEmpty()) continue;

                    // Random number of employees submit for this room (1-3)
                    $randomEmployees = $employees->random(min(rand(1, 3), $employees->count()));
                    
                    foreach ($randomEmployees as $employee) {
                        // Check if submission already exists
                        $existingSubmission = TaskSubmission::where('employee_id', $employee->id)
                            ->where('project_room_id', $room->id)
                            ->whereDate('tanggal', $date)
                            ->exists();

                        if ($existingSubmission) continue;

                        // Create submission
                        $submission = TaskSubmission::create([
                            'employee_id' => $employee->id,
                            'project_id' => $project->id,
                            'project_room_id' => $room->id,
                            'tanggal' => $date,
                            'submitted_at' => $date . ' ' . sprintf('%02d:%02d:%02d', rand(7, 17), rand(0, 59), rand(0, 59)),
                            'foto' => null, // No photo for seeder
                            'catatan' => rand(0, 1) ? 'Sudah dikerjakan dengan baik' : null,
                        ]);

                        // Create submission items for each task
                        foreach ($room->tasks as $task) {
                            TaskSubmissionItem::create([
                                'task_submission_id' => $submission->id,
                                'task_list_id' => $task->id,
                                'is_completed' => rand(0, 100) > 15, // 85% chance completed
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('✅ Task Submission seeder completed!');
    }
}
