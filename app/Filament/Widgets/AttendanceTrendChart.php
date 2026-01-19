<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Kehadiran (7 Hari Terakhir)';
    protected static ?int $sort = 2; // Tampil setelah Stats Overview

    protected function getData(): array
    {
        $dates = collect(range(6, 0))->map(fn ($days) => Carbon::now()->subDays($days)->format('Y-m-d'));
        
        $data = Attendance::where('status', 'hadir')
            ->whereDate('tanggal', '>=', Carbon::now()->subDays(6))
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->tanggal)->format('Y-m-d'))
            ->map(fn ($item) => $item->count());

        $counts = $dates->map(fn ($date) => $data->get($date, 0));

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kehadiran',
                    'data' => $counts->toArray(),
                    'fill' => true,
                    'borderColor' => '#10b981', // Emerald 500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $dates->map(fn ($date) => Carbon::parse($date)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
