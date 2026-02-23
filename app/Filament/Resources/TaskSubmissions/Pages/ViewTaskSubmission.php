<?php

namespace App\Filament\Resources\TaskSubmissions\Pages;

use App\Filament\Resources\TaskSubmissions\TaskSubmissionResource;
use App\Models\TaskSubmission;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTaskSubmission extends ViewRecord
{
    protected static string $resource = TaskSubmissionResource::class;

    protected static ?string $title = 'Detail Checkpoint';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Checkpoint')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tanggal')
                                    ->label('Tanggal')
                                    ->date('d F Y'),

                                TextEntry::make('submitted_time')
                                    ->label('Waktu')
                                    ->time('H:i'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Nama Petugas'),

                                TextEntry::make('project.nama_project')
                                    ->label('Project'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('room.nama_ruangan')
                                    ->label('Ruangan'),

                                TextEntry::make('room.lantai')
                                    ->label('Lantai'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_tasks')
                                    ->label('Total Tugas'),

                                TextEntry::make('completed_count')
                                    ->label('Selesai')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('completion_rate')
                                    ->label('Progress')
                                    ->formatStateUsing(fn (TaskSubmission $record): string => "{$record->completion_rate}%")
                                    ->badge()
                                    ->color(fn (TaskSubmission $record): string => match (true) {
                                        $record->completion_rate == 100 => 'success',
                                        $record->completion_rate >= 75 => 'info',
                                        $record->completion_rate >= 50 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),

                        // TextEntry::make('keterangan')
                        //     ->label('Keterangan')
                        //     ->placeholder('Tidak ada keterangan')
                        //     ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Foto Checkpoint')
                    ->schema([
                        ViewEntry::make('foto')
                            ->label('')
                            ->view('filament.resources.task-submissions.components.photo-display'),
                    ])
                    ->columnSpanFull(),

                Section::make('Checklist Tugas')
                    ->schema([
                        ViewEntry::make('items')
                            ->label('')
                            ->view('filament.resources.task-submissions.components.tasks-list'),
                    ])
                    ->columnSpanFull(),

                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('submitted_at')
                                    ->label('Dikirim Pada')
                                    ->dateTime('d F Y, H:i:s'),

                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d F Y, H:i:s'),
                            ]),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
