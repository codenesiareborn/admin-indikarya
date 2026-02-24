<?php

namespace App\Filament\Resources\TaskSubmissions\Tables;

use App\Models\TaskSubmission;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;

class TaskSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Petugas')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('room.nama_ruangan')
                    ->label('Ruangan')
                    ->searchable()
                    ->description(fn (TaskSubmission $record): string => 'Lantai ' . ($record->room?->lantai ?? '-')),

                Tables\Columns\TextColumn::make('completed_count')
                    ->label('Selesai')
                    ->formatStateUsing(fn (TaskSubmission $record): string => "{$record->completed_count}/{$record->total_tasks}")
                    ->badge()
                    ->color(fn (TaskSubmission $record): string => $record->completion_rate == 100 ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Progress')
                    ->formatStateUsing(fn (TaskSubmission $record): string => "{$record->completion_rate}%")
                    ->badge()
                    ->color(fn (TaskSubmission $record): string => match (true) {
                        $record->completion_rate == 100 => 'success',
                        $record->completion_rate >= 75 => 'info',
                        $record->completion_rate >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'nama_project'),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Petugas')
                    ->relationship('user', 'name'),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
