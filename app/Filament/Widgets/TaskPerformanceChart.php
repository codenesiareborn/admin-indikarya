<?php

namespace App\Filament\Widgets;

use App\Models\TaskSubmission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TaskPerformanceChart extends ChartWidget
{
    protected ?string $heading = '5 Project dengan Performa Task Terbaik';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Menggunakan Collection processing karena completion_rate adalah Accessor
        $data = TaskSubmission::with(['project', 'items'])
            ->get()
            ->groupBy('project_id')
            ->map(function ($submissions) {
                // Hitung rata-rata completion rate per project
                $avgRate = $submissions->avg(fn ($submission) => $submission->completion_rate);
                
                return [
                    'name' => $submissions->first()->project->nama_project ?? 'Unknown Project',
                    'rate' => round($avgRate, 1)
                ];
            })
            ->sortByDesc('rate')
            ->take(5);

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Completion Rate (%)',
                    'data' => $data->pluck('rate')->values()->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // Blue
                        '#8b5cf6', // Violet
                        '#ec4899', // Pink
                        '#f59e0b', // Amber
                        '#10b981', // Emerald
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
