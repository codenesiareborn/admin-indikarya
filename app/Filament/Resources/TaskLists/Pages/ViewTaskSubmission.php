<?php

namespace App\Filament\Resources\TaskLists\Pages;

use App\Filament\Resources\TaskLists\TaskListResource;
use App\Models\TaskSubmission;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ViewTaskSubmission extends ViewRecord
{
    protected static string $resource = TaskListResource::class;

    protected static ?string $title = 'Detail Task Submission';

    public function resolveRecord(int|string $key): TaskSubmission
    {
        return TaskSubmission::with(['employee', 'project', 'room', 'items.task'])
            ->findOrFail($key);
    }

    public function getRecordTitle(): string|Htmlable
    {
        return 'Detail Task Submission';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Submission')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('employee.nip')
                                    ->label('NIK'),
                                
                                TextEntry::make('employee.name')
                                    ->label('Nama Pegawai'),
                                
                                TextEntry::make('tanggal')
                                    ->label('Tanggal')
                                    ->date('d F Y'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('project.nama_project')
                                    ->label('Project'),
                                
                                TextEntry::make('room.nama_ruangan')
                                    ->label('Area/Ruangan'),
                                
                                TextEntry::make('submitted_at')
                                    ->label('Jam Submit')
                                    ->dateTime('H:i:s'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('completion_rate')
                                    ->label('Persentase Selesai')
                                    ->state(fn (TaskSubmission $record) => $record->completion_rate . '%')
                                    ->badge()
                                    ->color(fn (TaskSubmission $record) => $record->completion_rate >= 100 ? 'success' : ($record->completion_rate >= 50 ? 'warning' : 'danger')),
                                
                                TextEntry::make('task_summary')
                                    ->label('Task Selesai')
                                    ->state(fn (TaskSubmission $record) => $record->completed_count . ' / ' . $record->total_tasks . ' task'),
                            ]),
                    ]),
                
                Section::make('Foto Dokumentasi')
                    ->schema([
                        ViewEntry::make('foto')
                            ->label('')
                            ->view('filament.resources.tasklists.components.photo-display'),
                    ]),
                
                Section::make('Catatan')
                    ->schema([
                        TextEntry::make('catatan')
                            ->label('')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (TaskSubmission $record) => !empty($record->catatan)),
                    
                Section::make('Daftar Task')
                    ->schema([
                        ViewEntry::make('items')
                            ->label('')
                            ->view('filament.resources.tasklists.components.submission-task-table'),
                    ]),
            ]);
    }
}
