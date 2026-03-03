<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'Pegawai';

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canAttach(): bool
    {
        return true;
    }

    protected function canDetach(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->displayFormat('d/m/Y'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staf_label')
                    ->label('Staf')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cleaning Services' => 'info',
                        'Security Services' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),

                TextColumn::make('pivot.tanggal_mulai')
                    ->label('Mulai di Project')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('pivot.tanggal_selesai')
                    ->label('Selesai di Project')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status_pegawai_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Non-Aktif' => 'danger',
                        'Cuti' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('staf')
                    ->options([
                        'cleaning_services' => 'Cleaning Services',
                        'security_services' => 'Security Services',
                    ]),

                SelectFilter::make('status_pegawai')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                        'cuti' => 'Cuti',
                    ]),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Assign Pegawai')
                    ->icon('heroicon-o-user-plus')
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectSearchColumns(['name', 'nip'])
                    ->recordSelectOptionsQuery(function ($query) {
                        // Get current project
                        $project = $this->getOwnerRecord();

                        // Base query for employees
                        $query->whereIn('role', ['employee', 'staff', 'manager']);

                        // Filter by project type (jenis_project)
                        if ($project && $project->jenis_project) {
                            $query->where('staf', $project->jenis_project);
                        }

                        // Only show employees without active projects
                        $query->availableForAssignment();

                        return $query;
                    })
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->multiple()
                            ->rules([
                                Rule::callback(function ($attribute, $value, $fail) {
                                    if (empty($value)) {
                                        return;
                                    }

                                    $employeeIds = is_array($value) ? $value : [$value];

                                    foreach ($employeeIds as $employeeId) {
                                        $user = User::find($employeeId);

                                        if ($user && $user->hasActiveProject()) {
                                            $activeProject = $user->getActiveProject();
                                            $fail("Pegawai {$user->name} sudah memiliki project aktif: {$activeProject->nama_project}");
                                        }
                                    }
                                }),
                            ])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nip} - {$record->name} ({$record->staf_label})"),
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->required(),
                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->action(function (array $data, AttachAction $action): void {
                        $record = $this->getOwnerRecord();
                        $employeeIds = $data['recordId'] ?? [];

                        if (! is_array($employeeIds)) {
                            $employeeIds = [$employeeIds];
                        }

                        $failedEmployees = [];

                        foreach ($employeeIds as $employeeId) {
                            $user = User::find($employeeId);

                            if (! $user) {
                                continue;
                            }

                            // Auto-close previous active project if exists (before attaching new)
                            if ($user->hasActiveProject()) {
                                $previousProject = $user->getActiveProject();
                                if ($previousProject && $previousProject->id !== $record->id) {
                                    $user->projects()->updateExistingPivot($previousProject->id, [
                                        'tanggal_selesai' => now()->subDay()->toDateString(),
                                    ]);
                                } elseif ($previousProject && $previousProject->id === $record->id) {
                                    // User already assigned to this project
                                    $failedEmployees[] = "{$user->name} (sudah di {$previousProject->nama_project})";

                                    continue;
                                }
                            }

                            $record->employees()->attach($employeeId, [
                                'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
                                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                            ]);
                        }

                        if (! empty($failedEmployees)) {
                            Notification::make()
                                ->title('Beberapa pegawai tidak dapat di-assign')
                                ->body(implode(', ', $failedEmployees))
                                ->danger()
                                ->send();
                        }

                        if (count($failedEmployees) < count($employeeIds)) {
                            Notification::make()
                                ->title('Pegawai berhasil di-assign ke project')
                                ->success()
                                ->send();
                        }

                        $action->redirect($this->getUrl());
                    }),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Hapus dari Project'),
            ])
            ->bulkActions([
                DetachBulkAction::make()
                    ->label('Hapus yang dipilih'),
            ]);
    }
}
