<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample data
        return [
            [
                '12345',
                'Budi Santoso',
                'budi@example.com',
                '081234567890',
                'laki-laki',
                'cleaning_services',
                '1990-01-15',
                '2024-01-01',
                'Jl. Merdeka No. 1, Jakarta',
                'aktif',
                'employee',
            ],
            [
                '12346',
                'Siti Aminah',
                'siti@example.com',
                '081234567891',
                'perempuan',
                'security_services',
                '1992-05-20',
                '2024-02-01',
                'Jl. Sudirman No. 2, Jakarta',
                'aktif',
                'employee',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'nip',
            'nama_lengkap',
            'email',
            'no_hp',
            'jenis_kelamin',
            'staf',
            'tanggal_lahir',
            'tanggal_masuk',
            'alamat',
            'status_pegawai',
            'role',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
