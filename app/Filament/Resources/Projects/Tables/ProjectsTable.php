<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_project')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis_project_label')
                    ->label('Jenis Project')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cleaning Services' => 'success',
                        'Security Services' => 'warning',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nilai_kontrak')
                    ->label('Nilai Kontrak')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Aktif' => 'success',
                        'Selesai' => 'primary',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('id')
                    ->label('Presensi')
                    ->formatStateUsing(fn () => 'Cek Presensi')
                    ->url(fn (Project $record): string => route('filament.admin.resources.attendances.manage-project', ['project' => $record->id]))
                    ->color('info')
                    ->icon('heroicon-o-calendar-days'),
                
                Tables\Columns\TextColumn::make('task_list_link')
                    ->label('Task List')
                    ->state(fn () => 'Cek Task List')
                    ->url(fn (Project $record): string => route('filament.admin.resources.task-lists.manage-project', ['project' => $record->id]))
                    ->color('success')
                    ->icon('heroicon-o-clipboard-document-check'),
                
                Tables\Columns\TextColumn::make('patrol_link')
                    ->label('Patroli')
                    ->state(fn () => 'Cek Patroli')
                    ->url(fn (Project $record): string => route('filament.admin.resources.patrols.manage-project', ['project' => $record->id]))
                    ->color('warning')
                    ->icon('heroicon-o-shield-check'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_project')
                    ->options([
                        'cleaning_services' => 'Cleaning Services',
                        'security_services' => 'Security Services',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
