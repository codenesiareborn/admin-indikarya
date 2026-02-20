<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shift_name_display')
                    ->label('Shift')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($record) => $record->shift_name_display)
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label('Check In')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('check_in_photo')
                    ->label('Foto Masuk')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat Foto' : '-')
                    ->url(fn ($record) => $record->check_in_photo ? asset('storage/' . $record->check_in_photo) : null)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->icon('heroicon-o-photo'),
                TextColumn::make('check_in_location')
                    ->label('Lokasi Masuk')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude
                            ? 'Lihat Maps'
                            : '-'
                    )
                    ->url(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude
                            ? "https://www.google.com/maps?q={$record->check_in_latitude},{$record->check_in_longitude}"
                            : null
                    )
                    ->openUrlInNewTab()
                    ->color('success')
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('check_out_photo')
                    ->label('Foto Keluar')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat Foto' : '-')
                    ->url(fn ($record) => $record->check_out_photo ? asset('storage/' . $record->check_out_photo) : null)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->icon('heroicon-o-photo'),
                TextColumn::make('check_out_location')
                    ->label('Lokasi Keluar')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude
                            ? 'Lihat Maps'
                            : '-'
                    )
                    ->url(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude
                            ? "https://www.google.com/maps?q={$record->check_out_latitude},{$record->check_out_longitude}"
                            : null
                    )
                    ->openUrlInNewTab()
                    ->color('success')
                    ->icon('heroicon-o-map-pin'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'terlambat' => 'warning',
                        'izin' => 'info',
                        'sakit' => 'danger',
                        'alpha' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
