<?php

namespace App\Filament\Resources\TaskLists\Pages;

use App\Filament\Resources\TaskLists\TaskListResource;
use App\Filament\Resources\TaskLists\Widgets\TaskListStatsWidget;
use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\TaskSubmission;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ManageProjectTaskList extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = TaskListResource::class;

    protected static ?string $title = 'Manage Task List Project';

    protected string $view = 'filament.resources.tasklists.pages.manage-project-tasklist';

    public ?int $projectId = null;
    public ?Project $project = null;
    public ?string $filterDate = null;
    public ?int $filterRoom = null;

    public function mount(): void
    {
        $this->projectId = request()->query('project');
        
        if ($this->projectId) {
            $this->project = Project::find($this->projectId);
        }

        $this->filterDate = request()->query('date', now()->format('Y-m-d'));
        $this->filterRoom = request()->query('room');
    }

    public function getTitle(): string
    {
        return $this->project 
            ? "Task List Report - {$this->project->nama_project}"
            : 'Task List Report';
    }

    public function getSubheading(): ?string
    {
        return $this->project?->alamat_lengkap;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TaskSubmission::query()
                    ->when($this->projectId, fn (Builder $query) => $query->where('project_id', $this->projectId))
                    ->when($this->filterDate, fn (Builder $query) => $query->whereDate('tanggal', $this->filterDate))
                    ->when($this->filterRoom, fn (Builder $query) => $query->where('project_room_id', $this->filterRoom))
                    ->with(['employee', 'project', 'room', 'items'])
            )
            ->columns([
                TextColumn::make('employee.nama_lengkap')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('room.nama_ruangan')
                    ->label('Area/Ruangan')
                    ->searchable()
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
                    ->label('Task Selesai')
                    ->state(fn (TaskSubmission $record) => "{$record->completed_count}/{$record->total_tasks}")
                    ->badge()
                    ->color(fn (TaskSubmission $record) => $record->completion_rate >= 100 ? 'success' : ($record->completion_rate >= 50 ? 'warning' : 'danger')),
                
                TextColumn::make('completion_rate')
                    ->label('Persentase')
                    ->state(fn (TaskSubmission $record) => "{$record->completion_rate}%")
                    ->badge()
                    ->color(fn (TaskSubmission $record) => $record->completion_rate >= 100 ? 'success' : ($record->completion_rate >= 50 ? 'warning' : 'danger')),
                
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->disk('public')
                    ->height(50)
                    ->defaultImageUrl(url('/images/no-image.png')),
                
                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('project_room_id')
                    ->label('Area/Ruangan')
                    ->options(fn () => $this->project 
                        ? ProjectRoom::where('project_id', $this->projectId)->pluck('nama_ruangan', 'id')
                        : []
                    ),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function getStats(): array
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
            'active_employees' => $submissions->pluck('user_id')->unique()->count(),
        ];
    }

    public function getRooms(): array
    {
        if (!$this->projectId) {
            return [];
        }

        return ProjectRoom::where('project_id', $this->projectId)
            ->where('status', 'aktif')
            ->pluck('nama_ruangan', 'id')
            ->toArray();
    }
}
