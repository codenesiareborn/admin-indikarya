<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        if (!$employee->user_id) {
            $password = $this->generateRandomPassword();
            
            $user = User::create([
                'name' => $employee->nama_lengkap,
                'email' => $employee->email,
                'password' => Hash::make($password),
            ]);
            
            $employee->user_id = $user->id;
            $employee->saveQuietly();
            
            session()->put('employee_password_' . $employee->id, $password);
        }
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        if ($employee->user) {
            $employee->user->update([
                'name' => $employee->nama_lengkap,
                'email' => $employee->email,
            ]);
        }
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        if ($employee->user) {
            $employee->user->delete();
        }
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }

    /**
     * Generate random password (8 characters: letters + numbers)
     */
    private function generateRandomPassword(): string
    {
        return Str::random(4) . rand(1000, 9999);
    }
}
