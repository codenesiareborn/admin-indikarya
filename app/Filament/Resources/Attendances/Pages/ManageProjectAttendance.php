<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\Project;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Attendances\Widgets\AttendanceStatsWidget;

class ManageProjectAttendance extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Manage Presensi Project';

    protected string $view = 'filament.resources.attendances.pages.manage-project-attendance';

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
            ? "Manage Presensi - {$this->project->nama_project}"
            : 'Manage Presensi Project';
    }

    public function getSubheading(): ?string
    {
        return $this->project?->alamat_lengkap;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->when($this->projectId, fn (Builder $query) => $query->where('project_id', $this->projectId))
                    ->when($this->filterDate, fn (Builder $query) => $query->whereDate('tanggal', $this->filterDate))
                    ->with(['employee', 'project'])
            )
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('employee.nama_lengkap')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('check_in')
                    ->label('Check In')
                    ->time('H:i')
                    ->placeholder('-'),
                
                ImageColumn::make('check_in_photo')
                    ->label('Foto Masuk')
                    ->disk('public')
                    ->height(40)
                    ->defaultImageUrl(url('/images/no-image.png')),
                
                TextColumn::make('check_in_location')
                    ->label('Lokasi Masuk')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude
                            ? '📍 GPS'
                            : '-'
                    )
                    ->url(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude
                            ? "https://www.google.com/maps?q={$record->check_in_latitude},{$record->check_in_longitude}"
                            : null
                    )
                    ->openUrlInNewTab(),
                
                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->time('H:i')
                    ->placeholder('-'),
                
                ImageColumn::make('check_out_photo')
                    ->label('Foto Keluar')
                    ->disk('public')
                    ->height(40)
                    ->defaultImageUrl(url('/images/no-image.png')),
                
                TextColumn::make('check_out_location')
                    ->label('Lokasi Keluar')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude
                            ? '📍 GPS'
                            : '-'
                    )
                    ->url(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude
                            ? "https://www.google.com/maps?q={$record->check_out_latitude},{$record->check_out_longitude}"
                            : null
                    )
                    ->openUrlInNewTab(),
                
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
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpha' => 'Alpha',
                    ]),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal', $date),
                        );
                    }),
            ])
            ->defaultSort('tanggal', 'desc')
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function getStats(): array
    {
        if (!$this->projectId || !$this->filterMonth) {
            return [
                'hadir' => 0,
                'terlambat' => 0,
                'tidak_hadir' => 0,
                'presentase' => 0,
            ];
        }

        $startDate = date('Y-m-01', strtotime($this->filterMonth));
        $endDate = date('Y-m-t', strtotime($this->filterMonth));

        $attendances = Attendance::where('project_id', $this->projectId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $hadir = $attendances->where('status', 'hadir')->count();
        $terlambat = $attendances->where('status', 'terlambat')->count();
        $tidakHadir = $attendances->whereIn('status', ['alpha', 'izin', 'sakit'])->count();
        
        $totalHariKerja = $attendances->count();
        $presentase = $totalHariKerja > 0 
            ? round((($hadir + $terlambat) / $totalHariKerja) * 100, 1)
            : 0;

        return [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'tidak_hadir' => $tidakHadir,
            'presentase' => $presentase,
        ];
    }
}
