<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PicsRelationManager extends RelationManager
{
    protected static string $relationship = 'pics';
    
    protected static ?string $title = 'PIC (Person In Charge)';

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
                DatePicker::make('assigned_at')
                    ->label('Tanggal Assign')
                    ->displayFormat('d/m/Y'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                
                TextColumn::make('pivot.assigned_at')
                    ->label('Tanggal Assign')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Assign PIC')
                    ->icon('heroicon-o-user-plus')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelectOptionsQuery(fn ($query) => $query->role('pic'))
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                        DatePicker::make('assigned_at')
                            ->label('Tanggal Assign')
                            ->displayFormat('d/m/Y')
                            ->default(now()),
                    ]),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Hapus PIC'),
            ])
            ->bulkActions([
                DetachBulkAction::make()
                    ->label('Hapus yang dipilih'),
            ]);
    }
}
