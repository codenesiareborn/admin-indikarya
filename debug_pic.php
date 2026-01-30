<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'pic@gmail.com')->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User ID: " . $user->id . "\n";
echo "User Name: " . $user->name . "\n";
echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
echo "isPic(): " . ($user->isPic() ? 'Yes' : 'No') . "\n";
echo "PIC Projects: " . $user->picProjects->pluck('nama_project')->join(', ') . "\n";
echo "PIC Project IDs: " . json_encode($user->getPicProjectIds()) . "\n";

// Check project_pics table
$picAssignments = \DB::table('project_pics')->get();
echo "\n--- All PIC Assignments ---\n";
foreach ($picAssignments as $pic) {
    $project = \App\Models\Project::find($pic->project_id);
    $picUser = \App\Models\User::find($pic->user_id);
    echo "Project: " . ($project->nama_project ?? 'N/A') . " | User: " . ($picUser->email ?? 'N/A') . "\n";
}
