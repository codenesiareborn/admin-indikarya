<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected Collection $data;
    protected array $stats;
    protected array $settings;
    protected string $startDate;
    protected string $endDate;
    protected string $reportNumber;

    public function __construct(Collection $data, array $stats, array $settings, string $startDate, string $endDate, string $reportNumber)
    {
        $this->data = $data;
        $this->stats = $stats;
        $this->settings = $settings;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportNumber = $reportNumber;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Pegawai',
            'Project',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Status',
            'Keterangan',
        ];
    }

    public function map($attendance): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $attendance->employee->nip ?? '-',
            $attendance->employee->nama_lengkap ?? '-',
            $attendance->project->nama_project ?? '-',
            $attendance->tanggal?->format('d/m/Y') ?? '-',
            $attendance->check_in?->format('H:i') ?? '-',
            $attendance->check_out?->format('H:i') ?? '-',
            $attendance->status_label ?? '-',
            $attendance->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Presensi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert header rows
                $sheet->insertNewRowBefore(1, 5);
                
                $companyName = $this->settings['company_name'] ?? 'PT Indikarya';
                $companyAddress = $this->settings['company_address'] ?? '';
                
                $sheet->setCellValue('A1', $companyName);
                $sheet->setCellValue('A2', $companyAddress);
                $sheet->setCellValue('A3', '');
                $sheet->setCellValue('A4', 'LAPORAN PRESENSI PEGAWAI');
                $sheet->setCellValue('A5', "Periode: {$this->startDate} s/d {$this->endDate} | No: {$this->reportNumber}");
                
                // Style header
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
                
                // Auto-fit columns
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Add summary at end
                $lastRow = $sheet->getHighestRow() + 2;
                $sheet->setCellValue("A{$lastRow}", 'RINGKASAN:');
                $sheet->getStyle("A{$lastRow}")->getFont()->setBold(true);
                
                $lastRow++;
                $sheet->setCellValue("A{$lastRow}", "Total Data: {$this->stats['total']} | Hadir: {$this->stats['hadir']} | Terlambat: {$this->stats['terlambat']} | Izin: {$this->stats['izin']} | Sakit: {$this->stats['sakit']} | Alpha: {$this->stats['alpha']}");
                
                $lastRow += 2;
                $sheet->setCellValue("A{$lastRow}", "Dicetak: " . now()->format('d/m/Y H:i'));
            },
        ];
    }
}
