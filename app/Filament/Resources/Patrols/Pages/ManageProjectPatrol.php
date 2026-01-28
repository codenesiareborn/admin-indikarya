<?php

namespace App\Filament\Resources\Patrols\Pages;

use App\Filament\Resources\Patrols\PatrolResource;
use App\Models\Patrol;
use App\Models\Project;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class ManageProjectPatrol extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = PatrolResource::class;

    protected static ?string $title = 'Laporan Patroli Project';

    protected string $view = 'filament.resources.patrols.pages.manage-project-patrol';

    public ?int $projectId = null;
    public ?Project $project = null;
    public ?string $filterDate = null;
    public ?string $filterMonth = null;

    public function mount(): void
    {
        $this->projectId = request()->query('project');
        
        if ($this->projectId) {
            $this->project = Project::find($this->projectId);
        }

        $this->filterDate = request()->query('date', now()->format('Y-m-d'));
        $this->filterMonth = request()->query('month', now()->format('Y-m'));
    }

    public function getTitle(): string
    {
        return $this->project 
            ? "Laporan Patroli - {$this->project->nama_project}"
            : 'Laporan Patroli Project';
    }

    public function getSubheading(): ?string
    {
        return $this->project?->alamat_lengkap;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Patrol::query()
                    ->when($this->projectId, fn (Builder $query) => $query->where('project_id', $this->projectId))
                    ->when($this->filterDate, fn (Builder $query) => $query->whereDate('patrol_date', $this->filterDate))
                    ->with(['user', 'project', 'patrolArea'])
            )
            ->columns([
                TextColumn::make('patrol_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),
                
                TextColumn::make('user.name')
                    ->label('Petugas')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('area_name')
                    ->label('Area')
                    ->searchable()
                    ->description(fn (Patrol $record): string => $record->area_code ?? ''),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aman' => 'success',
                        'Tidak Aman' => 'danger',
                        default => 'gray',
                    }),
                
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->height(50)
                    ->defaultImageUrl(url('/images/no-image.png')),
                
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('-'),
                
                TextColumn::make('submitted_at')
                    ->label('Dikirim')
                    ->dateTime('H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Aman' => 'Aman',
                        'Tidak Aman' => 'Tidak Aman',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Petugas')
                    ->relationship('user', 'name'),
            ])
            ->defaultSort('patrol_time', 'desc')
            ->actions([
                ViewAction::make()
                    ->url(fn (Patrol $record): string => PatrolResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function getStats(): array
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
