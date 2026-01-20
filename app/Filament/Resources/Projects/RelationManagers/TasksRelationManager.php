<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;


class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';
    
    protected static ?string $title = 'Task List';

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    protected function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_room_id')
                    ->label('Ruangan')
                    ->options(fn () => $this->getOwnerRecord()->rooms()->pluck('nama_ruangan', 'id'))
                    ->required()
                    ->searchable(),
                
                TextInput::make('nama_task')
                    ->label('Nama Task')
                    ->required()
                    ->maxLength(255),
                
                
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3),
                
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                    ])
                    ->default('aktif')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_task')
            ->columns([
                TextColumn::make('urutan')
                    ->label('No')
                    ->sortable()
                    ->badge(),
                
                TextColumn::make('nama_task')
                    ->label('Nama Task')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->placeholder('-'),
                
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Non-Aktif' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('room.nama_ruangan')
                    ->label('Ruangan')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('room.nama_ruangan')
            ->defaultSort('urutan')
            ->filters([

                SelectFilter::make('project_room_id')
                    ->label('Ruangan')
                    ->options(fn () => $this->getOwnerRecord()->rooms()->pluck('nama_ruangan', 'id')),
                
                SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Task')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus yang dipilih'),
            ]);
    }
}
