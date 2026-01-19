<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\TaskSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');
        
        $hadir = Attendance::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $terlambat = Attendance::whereDate('tanggal', $today)->where('status', 'terlambat')->count();
        $izinSakit = Attendance::whereDate('tanggal', $today)->whereIn('status', ['izin', 'sakit'])->count();
        $taskSubmissions = TaskSubmission::whereDate('tanggal', $today)->count();

        return [
            Stat::make('Kehadiran Hari Ini', $hadir)
                ->description('Pegawai check-in tepat waktu')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, $hadir]), // Dummy trend for visual appeal or real if needed

            Stat::make('Terlambat', $terlambat)
                ->description('Pegawai check-in terlambat')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Izin / Sakit', $izinSakit)
                ->description('Pegawai tidak masuk')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('danger'),

            Stat::make('Laporan Masuk', $taskSubmissions)
                ->description('Task submission hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),
        ];
    }
}
