<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromView, ShouldAutoSize, WithStyles
{
    protected Collection $data;
    protected array $stats;
    protected array $settings;
    protected string $startDate;
    protected string $endDate;
    protected string $reportNumber;
    protected string $projectName;

    public function __construct(Collection $data, array $stats, array $settings, string $startDate, string $endDate, string $reportNumber, string $projectName)
    {
        $this->data = $data;
        $this->stats = $stats;
        $this->settings = $settings;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportNumber = $reportNumber;
        $this->projectName = $projectName;
    }

    public function view(): View
    {
        return view('reports.attendance-report-excel', [
            'data' => $this->data,
            'stats' => $this->stats,
            'settings' => $this->settings,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportNumber' => $this->reportNumber,
            'projectName' => $this->projectName,
            'isExcel' => true,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
