<?php

namespace App\Filament\Resources\Attendances\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class AttendanceStatsWidget extends BaseWidget
{
    #[Reactive]
    public ?int $projectId = null;
    
    #[Reactive]
    public ?string $filterMonth = null;

    protected function getStats(): array
    {
        $stats = $this->calculateStats();

        return [
            Stat::make('Hadir Tepat Waktu', $stats['hadir'])
                ->description('Total hadir tepat waktu')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Terlambat', $stats['terlambat'])
                ->description('Total terlambat')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Tidak Hadir', $stats['tidak_hadir'])
                ->description('Alpha, Izin, Sakit')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            
            Stat::make('Presentase Kehadiran', $stats['presentase'] . '%')
                ->description('Tingkat kehadiran bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }

    protected function calculateStats(): array
    {
        if (!$this->projectId || !$this->filterMonth) {
            return [
                'hadir' => 0,
                'terlambat' => 0,
                'tidak_hadir' => 0,
                'presentase' => 0,
            ];
        }

        $startDate = date('Y-m-01', strtotime($this->filterMonth));
        $endDate = date('Y-m-t', strtotime($this->filterMonth));

        $attendances = Attendance::where('project_id', $this->projectId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $hadir = $attendances->where('status', 'hadir')->count();
        $terlambat = $attendances->where('status', 'terlambat')->count();
        $tidakHadir = $attendances->whereIn('status', ['alpha', 'izin', 'sakit'])->count();
        
        $totalHariKerja = $attendances->count();
        $presentase = $totalHariKerja > 0 
            ? round((($hadir + $terlambat) / $totalHariKerja) * 100, 1)
            : 0;

        return [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'tidak_hadir' => $tidakHadir,
            'presentase' => $presentase,
        ];
    }
}
