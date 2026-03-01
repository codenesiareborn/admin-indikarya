<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Exports\AttendanceExport;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\GeneralSetting;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Laporan Presensi Pegawai';

    protected string $view = 'filament.resources.attendances.pages.attendance-report';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $projectId = null;
    public ?string $status = null;
    public ?string $projectType = null;
    public ?string $employeeId = null;
    public array $employees = [];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Cache employees list to avoid repeated queries
        $this->employees = $this->getEmployees();
        
        // Set employee filter from URL parameter if exists
        if (request()->has('employeeId')) {
            $this->employeeId = request()->get('employeeId');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->defaultSort('tanggal', 'desc')
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
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
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('shift_name_display')
                    ->label('Shift')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('check_in')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-'),
                
                TextColumn::make('check_out')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->placeholder('-'),
                
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Terlambat' => 'warning',
                        'Izin' => 'info',
                        'Sakit' => 'danger',
                        'Alpha' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->actions([
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Attendance $record) => AttendanceResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected function getFilteredQuery(): Builder
    {
        $user = auth()->user();
        
        $query = Attendance::query()
            ->with(['employee', 'project'])
            ->when($this->startDate, fn (Builder $q) => $q->whereDate('tanggal', '>=', $this->startDate))
            ->when($this->endDate, fn (Builder $q) => $q->whereDate('tanggal', '<=', $this->endDate))
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
        
        return [
            'total' => $query->count(),
            'hadir' => (clone $query)->where('status', 'hadir')->count(),
            'terlambat' => (clone $query)->where('status', 'terlambat')->count(),
            'izin' => (clone $query)->where('status', 'izin')->count(),
            'sakit' => (clone $query)->where('status', 'sakit')->count(),
            'alpha' => (clone $query)->where('status', 'alpha')->count(),
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

    public function getProjectTypes(): array
    {
        return [
            'cleaning_services' => 'Cleaning Services',
            'security_services' => 'Security Services',
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
        $attendances = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => GeneralSetting::get('company_address', ''),
        ];
        
        $startDate = $this->startDate ? \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') : '-';
        $endDate = $this->endDate ? \Carbon\Carbon::parse($this->endDate)->format('d/m/Y') : '-';
        $reportNumber = 'ATT-' . now()->format('Ymd-His');
        
        return Excel::download(
            new AttendanceExport($attendances, $stats, $settings, $startDate, $endDate, $reportNumber),
            'laporan-presensi-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $attendances = $this->getFilteredQuery()->get();
        $stats = $this->getStats();
        $settings = [
            'company_name' => GeneralSetting::get('company_name', 'PT Indikarya Total Solution'),
            'company_address' => GeneralSetting::get('company_address', ''),
            'company_phone' => GeneralSetting::get('company_phone', ''),
            'company_email' => GeneralSetting::get('company_email', ''),
        ];
        
        $reportNumber = 'LAP-ABS-' . now()->format('Ymd-His');
        
        $pdf = Pdf::loadView('reports.attendance-report', [
            'data' => $attendances,
            'stats' => $stats,
            'settings' => $settings,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportNumber' => $reportNumber,
        ]);
        
        $pdf->setPaper('a4', 'landscape');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-presensi-' . now()->format('Y-m-d') . '.pdf');
    }
}
