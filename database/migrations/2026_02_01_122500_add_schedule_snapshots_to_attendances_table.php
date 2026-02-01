<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Store the schedule times at the moment of attendance
            // This ensures history remains consistent even if project schedule changes
            $table->time('jam_masuk_snapshot')->nullable()->after('check_out_address');
            $table->time('jam_pulang_snapshot')->nullable()->after('jam_masuk_snapshot');
            
            // Index for faster queries when filtering by schedule
            $table->index(['jam_masuk_snapshot', 'jam_pulang_snapshot'], 'idx_attendance_schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_schedules');
            $table->dropColumn(['jam_masuk_snapshot', 'jam_pulang_snapshot']);
        });
    }
};
