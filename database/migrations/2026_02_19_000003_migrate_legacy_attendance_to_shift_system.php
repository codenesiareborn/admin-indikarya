<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For legacy data: set shift_name_snapshot to "Main Shift" 
        // shift_id remains null for legacy data (no master shift record created)
        $attendanceUpdated = Attendance::whereNull('shift_name_snapshot')
            ->update([
                'shift_name_snapshot' => 'Main Shift',
            ]);

        \Log::info("Legacy attendance migration completed: {$attendanceUpdated} attendance records updated with 'Main Shift' snapshot.");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Main Shift snapshot from legacy records
        Attendance::where('shift_name_snapshot', 'Main Shift')
            ->whereNull('shift_id')
            ->update([
                'shift_name_snapshot' => null,
            ]);
    }
};
