<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable;

    protected $errors = [];
    protected $successCount = 0;
    protected $failureCount = 0;

    public function model(array $row)
    {
        // Generate password from NIP (password = NIP)
        $password = $row['nip'];

        $user = new User([
            'nip' => $row['nip'],
            'name' => $row['nama_lengkap'],
            'email' => $row['email'],
            'password' => Hash::make($password),
            'no_hp' => $row['no_hp'] ?? null,
            'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'staf' => $row['staf'] ?? null,
            'tanggal_lahir' => !empty($row['tanggal_lahir']) ? $this->parseDate($row['tanggal_lahir']) : null,
            'tanggal_masuk' => !empty($row['tanggal_masuk']) ? $this->parseDate($row['tanggal_masuk']) : null,
            'alamat' => $row['alamat'] ?? null,
            'status_pegawai' => $row['status_pegawai'] ?? 'aktif',
            'role' => $row['role'] ?? 'employee',
            'is_active' => true,
        ]);

        $this->successCount++;
        return $user;
    }

    public function rules(): array
    {
        return [
            'nip' => ['required', 'unique:users,nip'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
            'staf' => ['nullable', Rule::in(['cleaning_services', 'security_services'])],
            'tanggal_lahir' => ['nullable'],
            'tanggal_masuk' => ['nullable'],
            'alamat' => ['nullable', 'string'],
            'status_pegawai' => ['nullable', Rule::in(['aktif', 'non-aktif', 'cuti'])],
            'role' => ['nullable', Rule::in(['employee', 'admin', 'super_admin'])],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nip.required' => 'NIP wajib diisi',
            'nip.unique' => 'NIP sudah terdaftar',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'jenis_kelamin.in' => 'Jenis kelamin harus: laki-laki atau perempuan',
            'staf.in' => 'Staf harus: cleaning_services atau security_services',
            'status_pegawai.in' => 'Status pegawai harus: aktif, non-aktif, atau cuti',
            'role.in' => 'Role harus: employee, admin, atau super_admin',
        ];
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        $this->failureCount++;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            $this->failureCount++;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    protected function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Try different date formats
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
        
        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed !== false) {
                return $parsed->format('Y-m-d');
            }
        }

        // If Excel serial date number
        if (is_numeric($date)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
        }

        return null;
    }
}
