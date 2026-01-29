<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AttendancePhotoSeeder extends Seeder
{
    public function run(): void
    {
        // Create a simple placeholder image (1x1 pixel PNG)
        $placeholderImage = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        
        // Create folder if not exists
        Storage::disk('public')->makeDirectory('attendances');

        // Get attendances without photos (limit to 10 for testing)
        $attendances = Attendance::whereNull('check_in_photo')
            ->orWhereNull('check_out_photo')
            ->limit(20)
            ->get();

        if ($attendances->isEmpty()) {
            $this->command->info('No attendances without photos found.');
            return;
        }

        $count = 0;
        foreach ($attendances as $attendance) {
            // Generate unique filenames
            $checkInFilename = "attendances/check_in_{$attendance->id}_" . time() . ".png";
            $checkOutFilename = "attendances/check_out_{$attendance->id}_" . time() . ".png";
            
            // Save placeholder images
            Storage::disk('public')->put($checkInFilename, $placeholderImage);
            Storage::disk('public')->put($checkOutFilename, $placeholderImage);
            
            // Update attendance record
            $attendance->update([
                'check_in_photo' => $checkInFilename,
                'check_out_photo' => $checkOutFilename,
                'check_in_latitude' => -6.2088 + (rand(-100, 100) / 10000),
                'check_in_longitude' => 106.8456 + (rand(-100, 100) / 10000),
                'check_out_latitude' => -6.2088 + (rand(-100, 100) / 10000),
                'check_out_longitude' => 106.8456 + (rand(-100, 100) / 10000),
            ]);
            
            $count++;
        }

        $this->command->info("✅ Updated {$count} attendance records with placeholder photos!");
    }
}
