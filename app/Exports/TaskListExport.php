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

class TaskListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
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
            'Area',
            'Tanggal',
            'Jam Submit',
            'Task Selesai',
            'Total Task',
            'Persentase',
            'Catatan',
        ];
    }

    public function map($submission): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $submission->employee->nip ?? '-',
            $submission->employee->nama_lengkap ?? '-',
            $submission->project->nama_project ?? '-',
            $submission->room->nama_ruangan ?? '-',
            $submission->tanggal?->format('d/m/Y') ?? '-',
            $submission->submitted_at?->format('H:i:s') ?? '-',
            $submission->completed_count,
            $submission->total_tasks,
            $submission->completion_rate . '%',
            $submission->catatan ?? '-',
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
        return 'Laporan Task List';
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
                $sheet->setCellValue('A4', 'LAPORAN TASK LIST');
                $sheet->setCellValue('A5', "Periode: {$this->startDate} s/d {$this->endDate} | No: {$this->reportNumber}");
                
                // Style header
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
                
                // Auto-fit columns
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Add summary at end
                $lastRow = $sheet->getHighestRow() + 2;
                $sheet->setCellValue("A{$lastRow}", 'RINGKASAN:');
                $sheet->getStyle("A{$lastRow}")->getFont()->setBold(true);
                
                $lastRow++;
                $sheet->setCellValue("A{$lastRow}", "Total Submit: {$this->stats['total_submissions']} | Task Selesai: {$this->stats['total_completed']} | Task Pending: {$this->stats['total_pending']} | Completion Rate: {$this->stats['completion_rate']}%");
                
                $lastRow += 2;
                $sheet->setCellValue("A{$lastRow}", "Dicetak: " . now()->format('d/m/Y H:i'));
            },
        ];
    }
}
