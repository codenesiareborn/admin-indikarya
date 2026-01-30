<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShiftReportExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $stats;
    protected $settings;
    protected $startDate;
    protected $endDate;
    protected $reportNumber;

    public function __construct($data, $stats, $settings, $startDate, $endDate, $reportNumber)
    {
        $this->data = $data;
        $this->stats = $stats;
        $this->settings = $settings;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportNumber = $reportNumber;
    }

    public function view(): View
    {
        return view('reports.shift-report', [
            'data' => $this->data,
            'stats' => $this->stats,
            'settings' => $this->settings,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportNumber' => $this->reportNumber,
            'isExcel' => true,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
