<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class EmployeeImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $successCount = 0;
    private int $failureCount = 0;
    private array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena index 0 dan ada header row
            
            try {
                // Validasi data
                $validator = Validator::make($row->toArray(), [
                    'nip' => 'required|string|max:50|unique:users,nip',
                    'nama_lengkap' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email',
                    'no_hp' => 'nullable|string|max:20',
                    'jenis_kelamin' => 'required|in:laki-laki,perempuan',
                    'staf' => 'required|in:cleaning_services,security_services',
                    'tanggal_lahir' => 'nullable|date',
                    'tanggal_masuk' => 'nullable|date',
                    'alamat' => 'nullable|string',
                    'status_pegawai' => 'required|in:aktif,non-aktif,cuti',
                    'role' => 'required|in:employee,pic,admin,super_admin',
                ], [
                    'nip.required' => 'NIP wajib diisi',
                    'nip.unique' => 'NIP sudah terdaftar',
                    'nama_lengkap.required' => 'Nama lengkap wajib diisi',
                    'email.required' => 'Email wajib diisi',
                    'email.email' => 'Format email tidak valid',
                    'email.unique' => 'Email sudah terdaftar',
                    'jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
                    'jenis_kelamin.in' => 'Jenis kelamin harus laki-laki atau perempuan',
                    'staf.required' => 'Staf wajib diisi',
                    'staf.in' => 'Staf harus cleaning_services atau security_services',
                    'status_pegawai.required' => 'Status pegawai wajib diisi',
                    'status_pegawai.in' => 'Status pegawai harus aktif, non-aktif, atau cuti',
                    'role.required' => 'Role wajib diisi',
                    'role.in' => 'Role harus employee, pic, admin, atau super_admin',
                ]);

                if ($validator->fails()) {
                    $this->failureCount++;
                    $errorMessages = implode(', ', $validator->errors()->all());
                    $this->errors[] = "Baris {$rowNumber}: {$errorMessages}";
                    continue;
                }

                // Buat user baru
                User::create([
                    'nip' => $row['nip'],
                    'name' => $row['nama_lengkap'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['nip']), // Password default = NIP
                    'no_hp' => $row['no_hp'] ?? null,
                    'jenis_kelamin' => $row['jenis_kelamin'],
                    'staf' => $row['staf'],
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'tanggal_masuk' => $row['tanggal_masuk'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'status_pegawai' => $row['status_pegawai'],
                    'role' => $row['role'],
                    'is_active' => true,
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
