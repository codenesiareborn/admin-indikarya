<?php

namespace App\Filament\Resources\ShiftReports\Pages;

use App\Filament\Resources\ShiftReports\ShiftReportResource;
use App\Models\ShiftReport;
use App\Models\Project;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ShiftReportPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = ShiftReportResource::class;

    protected static ?string $title = 'Laporan Shift';

    protected string $view = 'filament.resources.shift-reports.pages.shift-report';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $projectId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Check if project_id is passed via query string (e.g. from Project List)
        if (request()->has('project_id')) {
            $this->projectId = request()->get('project_id');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Personil')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->sortable(),
                
                TextColumn::make('shift_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('shift_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('report')
                    ->label('Laporan')
                    ->limit(50)
                    ->tooltip(fn (ShiftReport $record): string => $record->report),

                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('shift_date', 'desc');
    }

    protected function getFilteredQuery(): Builder
    {
        return ShiftReport::query()
            ->with(['user', 'project'])
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('shift_date', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('shift_date', '<=', $this->endDate))
            ->when($this->projectId, fn (Builder $q) => $q->where('project_id', $this->projectId));
    }

    public function getStats(): array
    {
        $query = $this->getFilteredQuery();
        $reports = $query->get();
        
        $total = $reports->count();
        
        return [
            'total' => $total,
            'active_officers' => $reports->pluck('user_id')->unique()->count(),
        ];
    }

    public function getProjects(): array
    {
        return Project::pluck('nama_project', 'id')->toArray();
    }
}
