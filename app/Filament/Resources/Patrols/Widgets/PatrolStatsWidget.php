<?php

namespace App\Filament\Resources\Patrols\Widgets;

use App\Models\Patrol;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class PatrolStatsWidget extends BaseWidget
{
    #[Reactive]
    public ?int $projectId = null;
    
    #[Reactive]
    public ?string $filterMonth = null;

    protected function getStats(): array
    {
        $stats = $this->calculateStats();

        return [
            Stat::make('Total Patroli', $stats['total'])
                ->description('Total patroli bulan ini')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('primary'),
            
            Stat::make('Status Aman', $stats['aman'])
                ->description('Area dalam kondisi aman')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Status Tidak Aman', $stats['tidak_aman'])
                ->description('Area perlu perhatian')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('Presentase Aman', $stats['presentase'] . '%')
                ->description('Tingkat keamanan bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($stats['presentase'] >= 80 ? 'success' : ($stats['presentase'] >= 50 ? 'warning' : 'danger')),
        ];
    }

    protected function calculateStats(): array
    {
        if (!$this->projectId || !$this->filterMonth) {
            return [
                'total' => 0,
                'aman' => 0,
                'tidak_aman' => 0,
                'presentase' => 0,
            ];
        }

        $startDate = date('Y-m-01', strtotime($this->filterMonth));
        $endDate = date('Y-m-t', strtotime($this->filterMonth));

        $patrols = Patrol::where('project_id', $this->projectId)
            ->whereBetween('patrol_date', [$startDate, $endDate])
            ->get();

        $total = $patrols->count();
        $aman = $patrols->where('status', 'Aman')->count();
        $tidakAman = $patrols->where('status', 'Tidak Aman')->count();
        
        $presentase = $total > 0 
            ? round(($aman / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'aman' => $aman,
            'tidak_aman' => $tidakAman,
            'presentase' => $presentase,
        ];
    }
}
