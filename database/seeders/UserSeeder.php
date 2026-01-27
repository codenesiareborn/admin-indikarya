<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@indikarya.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // 1. Create 10 Regular Users (Admin/Staff)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "User Admin {$i}",
                'email' => "admin{$i}@indikarya.com",
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        // 2. Create 50 Cleaning Services Employees
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => "Cleaning Staff {$i}",
                'email' => "cleaning{$i}@indikarya.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'nip' => 'CS-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'staf' => 'cleaning_services',
                'no_hp' => '08' . rand(1000000000, 9999999999),
                'tanggal_lahir' => now()->subYears(rand(20, 45)),
                'jenis_kelamin' => rand(0, 1) ? 'laki-laki' : 'perempuan',
                'tanggal_masuk' => now()->subMonths(rand(1, 24)),
                'alamat' => 'Jakarta, Indonesia',
                'status_pegawai' => 'aktif',
            ]);
        }

        // 3. Create 50 Security Services Employees
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => "Security Staff {$i}",
                'email' => "security{$i}@indikarya.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
                'nip' => 'SEC-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'staf' => 'security_services',
                'no_hp' => '08' . rand(1000000000, 9999999999),
                'tanggal_lahir' => now()->subYears(rand(20, 45)),
                'jenis_kelamin' => rand(0, 1) ? 'laki-laki' : 'perempuan',
                'tanggal_masuk' => now()->subMonths(rand(1, 24)),
                'alamat' => 'Jakarta, Indonesia',
                'status_pegawai' => 'aktif',
            ]);
        }

        $this->command->info('✅ Created Super Admin + 10 admins + 100 employees (50 cleaning + 50 security)');
    }
}
