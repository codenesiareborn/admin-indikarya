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
        // Return 2 sample data yang valid untuk import
        return [
            [
                'EMP001',
                'Ahmad Wijaya',
                'ahmad.wijaya@example.com',
                '081234567890',
                'laki-laki',
                'cleaning_services',
                '1990-05-15',
                '2024-01-10',
                'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'aktif',
                'employee',
            ],
            [
                'EMP002',
                'Dewi Lestari',
                'dewi.lestari@example.com',
                '081298765432',
                'perempuan',
                'security_services',
                '1995-08-22',
                '2024-02-15',
                'Jl. Thamrin No. 88, Jakarta Pusat',
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
