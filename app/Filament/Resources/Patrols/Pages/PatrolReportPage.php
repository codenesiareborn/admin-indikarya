<?php

namespace App\Filament\Resources\Patrols\Pages;

use App\Filament\Resources\Patrols\PatrolResource;
use App\Models\Patrol;
use App\Models\Project;
use App\Models\PatrolArea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class PatrolReportPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = PatrolResource::class;

    protected static ?string $title = 'Laporan Patroli';

    protected string $view = 'filament.resources.patrols.pages.patrol-report';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $projectId = null;
    public ?string $projectType = null;
    public ?string $status = null;
    public ?string $employeeId = null;
    public array $employees = [];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Cache employees list
        $this->employees = $this->getEmployees();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.name')
                    ->label('Nama Petugas')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->sortable(),

                TextColumn::make('project.jenis_project')
                    ->label('Jenis Project')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cleaning_services' => 'info',
                        'security_services' => 'warning',
                        'gardening_services' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                
                TextColumn::make('area_name')
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('patrol_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('patrol_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aman' => 'success',
                        'Tidak Aman' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('photo')
                    ->label('Foto')
                    ->formatStateUsing(fn ($state) => $state ? basename($state) : '-')
                    ->url(fn ($record) => $record->photo ? asset('storage/' . $record->photo) : null)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->icon('heroicon-o-photo'),

                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->defaultSort('patrol_date', 'desc')
            ->actions([
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => PatrolResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected function getFilteredQuery(): Builder
    {
        $user = auth()->user();
        
        $query = Patrol::query()
            ->with(['user', 'project', 'patrolArea'])
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('patrol_date', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('patrol_date', '<=', $this->endDate))
            ->when($this->projectId, fn (Builder $q) => $q->where('project_id', $this->projectId))
            ->when($this->status, fn (Builder $q) => $q->where('status', $this->status))
            ->when($this->projectType, fn (Builder $q) => $q->whereHas('project', fn($q) => $q->where('jenis_project', $this->projectType)))
            ->when($this->employeeId, fn (Builder $q) => $q->where('user_id', $this->employeeId));
        
        // Filter untuk PIC - hanya tampilkan data dari project yang di-assign
        if ($user && $user->isPic() && !$user->hasRole('super_admin') && !$user->hasRole('admin')) {
            $projectIds = $user->getPicProjectIds();
            $query->whereIn('project_id', $projectIds);
        }
        
        return $query;
    }

    public function getStats(): array
    {
        $query = $this->getFilteredQuery();
        $patrols = $query->get();
        
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
            'active_officers' => $patrols->pluck('user_id')->unique()->count(),
        ];
    }

    public function getProjects(): array
    {
        $user = auth()->user();
        $query = Project::query();
        
        if ($this->projectType) {
            $query->where('jenis_project', $this->projectType);
        }
        
        // Filter untuk PIC - hanya tampilkan project yang di-assign
        if ($user && $user->isPic() && !$user->hasRole('super_admin') && !$user->hasRole('admin')) {
            $projectIds = $user->getPicProjectIds();
            $query->whereIn('id', $projectIds);
        }
        
        return $query->pluck('nama_project', 'id')->toArray();
    }

    public function getStatuses(): array
    {
        return [
            'Aman' => 'Aman',
            'Tidak Aman' => 'Tidak Aman',
        ];
    }

    public function getProjectTypes(): array
    {
        return [
            'cleaning_services' => 'Cleaning Services',
            'security_services' => 'Security Services',
            'gardening_services' => 'Gardening Services',
        ];
    }

    public function getEmployees(): array
    {
        return \App\Models\User::query()
            ->where('role', 'employee')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function applyFilter(): void
    {
        $this->resetTable();
    }

    public function exportExcel()
    {
        $patrols = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => \App\Models\GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => \App\Models\GeneralSetting::get('company_address', ''),
        ];
        
        $reportNumber = 'PTR-' . now()->format('Ymd-His');
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PatrolExport($patrols, $stats, $settings, $this->startDate, $this->endDate, $reportNumber),
            'laporan-patroli-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $patrols = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => \App\Models\GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => \App\Models\GeneralSetting::get('company_address', ''),
            'company_phone' => \App\Models\GeneralSetting::get('company_phone', ''),
            'company_email' => \App\Models\GeneralSetting::get('company_email', ''),
        ];
        
        $reportNumber = 'LAP-PTR-' . now()->format('Ymd-His');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.patrol-report', [
            'data' => $patrols,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportNumber' => $reportNumber,
        ]);
        
        $pdf->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-patroli-' . now()->format('Y-m-d') . '.pdf');
    }
}
