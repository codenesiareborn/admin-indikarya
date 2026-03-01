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
    public ?string $employeeId = null;
    public array $employees = [];
    public ?string $employeeSearch = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Cache employees list
        $this->employees = $this->getEmployees();
        
        // Check if project_id is passed via query string (e.g. from Project List)
        if (request()->has('project_id')) {
            $this->projectId = request()->get('project_id');
        }
        
        // Check if employee_id is passed via query string
        if (request()->has('employeeId')) {
            $this->employeeId = request()->get('employeeId');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->searchable()
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
        $user = auth()->user();
        
        $query = ShiftReport::query()
            ->with(['user', 'project'])
            ->when($this->employeeSearch, function (Builder $q) {
                $q->whereHas('user', function (Builder $subQ) {
                    $subQ->where('name', 'like', '%' . $this->employeeSearch . '%');
                });
            })
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('shift_date', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('shift_date', '<=', $this->endDate))
            ->when($this->projectId, fn (Builder $q) => $q->where('project_id', $this->projectId))
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
        $reports = $query->get();
        
        $total = $reports->count();
        
        return [
            'total' => $total,
            'active_officers' => $reports->pluck('user_id')->unique()->count(),
        ];
    }

    public function getProjects(): array
    {
        $user = auth()->user();
        $query = Project::query();
        
        // Filter untuk PIC - hanya tampilkan project yang di-assign
        if ($user && $user->isPic() && !$user->hasRole('super_admin') && !$user->hasRole('admin')) {
            $projectIds = $user->getPicProjectIds();
            $query->whereIn('id', $projectIds);
        }
        
        return $query->pluck('nama_project', 'id')->toArray();
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
        $reports = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => \App\Models\GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => \App\Models\GeneralSetting::get('company_address', 'Perum Saka Permai No C 10, Plumbon, Sardonoharjo, Ngaglik, Sleman, Yogyakarta'),
        ];
        
        $reportNumber = 'SFT-' . now()->format('Ymd-His');
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ShiftReportExport($reports, $stats, $settings, $this->startDate, $this->endDate, $reportNumber),
            'laporan-shift-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $reports = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => \App\Models\GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => \App\Models\GeneralSetting::get('company_address', 'Perum Saka Permai No C 10, Plumbon, Sardonoharjo, Ngaglik, Sleman, Yogyakarta'),
            'company_phone' => \App\Models\GeneralSetting::get('company_phone', 'Telp.(0274)4362536, Hp.085729898968'),
            'company_email' => \App\Models\GeneralSetting::get('company_email', 'pt.indikarya@yahoo.com'),
        ];
        
        $reportNumber = 'LAP-SFT-' . now()->format('Ymd-His');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.shift-report', [
            'data' => $reports,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportNumber' => $reportNumber,
        ]);
        
        $pdf->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-shift-' . now()->format('Y-m-d') . '.pdf');
    }
}
