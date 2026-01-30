<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TaskList Count: " . \App\Models\TaskList::count() . PHP_EOL;
echo "TaskSubmission Count: " . \App\Models\TaskSubmission::count() . PHP_EOL;
