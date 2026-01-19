<?php

namespace App\Filament\Resources\TaskLists\Pages;

use App\Filament\Resources\TaskLists\TaskListResource;
use App\Models\GeneralSetting;
use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\TaskSubmission;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class TaskListReportPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = TaskListResource::class;

    protected static ?string $title = 'Laporan Task List';

    protected string $view = 'filament.resources.tasklists.pages.tasklist-report';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $projectId = null;
    public ?string $roomId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('employee.nama_lengkap')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->sortable(),
                
                TextColumn::make('room.nama_ruangan')
                    ->label('Area')
                    ->sortable(),
                
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('submitted_at')
                    ->label('Jam Submit')
                    ->dateTime('H:i:s')
                    ->sortable(),
                
                TextColumn::make('task_completion')
                    ->label('Task')
                    ->state(fn (TaskSubmission $record) => "{$record->completed_count}/{$record->total_tasks}")
                    ->badge()
                    ->color(fn (TaskSubmission $record) => $record->completion_rate >= 100 ? 'success' : ($record->completion_rate >= 50 ? 'warning' : 'danger')),
                
                TextColumn::make('completion_rate')
                    ->label('%')
                    ->state(fn (TaskSubmission $record) => "{$record->completion_rate}%"),
                
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->height(40),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    protected function getFilteredQuery(): Builder
    {
        return TaskSubmission::query()
            ->with(['employee', 'project', 'room', 'items'])
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('tanggal', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('tanggal', '<=', $this->endDate))
            ->when($this->projectId, fn (Builder $q) => $q->where('project_id', $this->projectId))
            ->when($this->roomId, fn (Builder $q) => $q->where('project_room_id', $this->roomId));
    }

    public function getStats(): array
    {
        $query = $this->getFilteredQuery();
        $submissions = $query->get();
        
        $totalCompleted = $submissions->sum(fn ($s) => $s->items->where('is_completed', true)->count());
        $totalPending = $submissions->sum(fn ($s) => $s->items->where('is_completed', false)->count());
        $totalTasks = $totalCompleted + $totalPending;
        $completionRate = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100, 1) : 0;
        
        return [
            'total_submissions' => $submissions->count(),
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
            'completion_rate' => $completionRate,
            'active_employees' => $submissions->pluck('employee_id')->unique()->count(),
        ];
    }

    public function getProjects(): array
    {
        return Project::pluck('nama_project', 'id')->toArray();
    }

    public function getRooms(): array
    {
        if (!$this->projectId) {
            return ProjectRoom::pluck('nama_ruangan', 'id')->toArray();
        }
        return ProjectRoom::where('project_id', $this->projectId)->pluck('nama_ruangan', 'id')->toArray();
    }

    public function applyFilter(): void
    {
        $this->resetTable();
    }
}
