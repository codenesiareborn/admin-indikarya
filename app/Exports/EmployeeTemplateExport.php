<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function array(): array
    {
        // Return 2 sample data yang valid untuk import (1 alphanumeric, 1 numeric)
        return [
            [
                'EMP001',
                'Ahmad Wijaya',
                'ahmad.wijaya@example.com',
                '081234567890',
                'laki-laki',
                'cleaning_services',
                '15-05-1990',
                '10-01-2024',
                'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'aktif',
                'employee',
            ],
            [
                '12345',
                'Dewi Lestari',
                'dewi.lestari@example.com',
                '081298765432',
                'perempuan',
                'security_services',
                '22-08-1995',
                '15-02-2024',
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

    public function title(): string
    {
        return 'Template Pegawai (DD-MM-YYYY)';
    }
}
