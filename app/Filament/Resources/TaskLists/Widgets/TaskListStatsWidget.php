<?php

namespace App\Filament\Resources\TaskLists\Widgets;

use App\Models\TaskSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class TaskListStatsWidget extends BaseWidget
{
    #[Reactive]
    public ?int $projectId = null;
    
    #[Reactive]
    public ?string $filterDate = null;

    protected function getStats(): array
    {
        $stats = $this->calculateStats();

        return [
            Stat::make('Total Submit', $stats['total_submissions'])
                ->description('Jumlah submit hari ini')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('primary'),
            
            Stat::make('Task Selesai', $stats['total_tasks_completed'])
                ->description('Total task dikerjakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Task Belum Selesai', $stats['total_tasks_pending'])
                ->description('Task belum dikerjakan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Completion Rate', $stats['completion_rate'] . '%')
                ->description('Persentase penyelesaian')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($stats['completion_rate'] >= 80 ? 'success' : ($stats['completion_rate'] >= 50 ? 'warning' : 'danger')),
            
            Stat::make('Pegawai Aktif', $stats['active_employees'])
                ->description('Jumlah pegawai submit hari ini')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }

    protected function calculateStats(): array
    {
        if (!$this->projectId || !$this->filterDate) {
            return [
                'total_submissions' => 0,
                'total_tasks_completed' => 0,
                'total_tasks_pending' => 0,
                'completion_rate' => 0,
                'active_employees' => 0,
            ];
        }

        $submissions = TaskSubmission::where('project_id', $this->projectId)
            ->whereDate('tanggal', $this->filterDate)
            ->with('items')
            ->get();

        $totalCompleted = $submissions->sum(fn ($s) => $s->items->where('is_completed', true)->count());
        $totalPending = $submissions->sum(fn ($s) => $s->items->where('is_completed', false)->count());
        $totalTasks = $totalCompleted + $totalPending;
        $completionRate = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100, 1) : 0;

        return [
            'total_submissions' => $submissions->count(),
            'total_tasks_completed' => $totalCompleted,
            'total_tasks_pending' => $totalPending,
            'completion_rate' => $completionRate,
            'active_employees' => $submissions->pluck('employee_id')->unique()->count(),
        ];
    }
}
