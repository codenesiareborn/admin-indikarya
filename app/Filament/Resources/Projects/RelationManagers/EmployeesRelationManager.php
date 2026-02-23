<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereIn('role', ['employee', 'staff', 'manager']))
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->multiple()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nip} - {$record->name} ({$record->staf_label})"),
                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->displayFormat('d/m/Y')
                            ->default(now()),
                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->displayFormat('d/m/Y'),
                    ]),
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
