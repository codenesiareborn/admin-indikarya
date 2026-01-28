<?php
use App\Models\TaskSubmission;
use App\Models\User;
use App\Models\Project;

echo "--- DEBUG INFO ---\n";
echo "TaskSubmission Count: " . TaskSubmission::count() . "\n";
echo "User Count: " . User::count() . "\n";
echo "Project Count: " . Project::count() . "\n";

$latest = TaskSubmission::latest()->first();
if ($latest) {
    echo "Latest Submission: ID=" . $latest->id . " Date=" . $latest->tanggal->format('Y-m-d') . "\n";
} else {
    echo "No submissions found.\n";
}

echo "Current Time: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Start of Month: " . now()->startOfMonth()->format('Y-m-d') . "\n";
