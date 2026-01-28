<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PatrolAreasRelationManager extends RelationManager
{
    protected static string $relationship = 'patrolAreas';
    
    protected static ?string $title = 'Area Patroli';

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
                TextInput::make('kode_area')
                    ->label('Kode Area')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: PA-001'),
                
                TextInput::make('nama_area')
                    ->label('Nama Area')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Lobby Utama'),
                
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi singkat area patroli'),
                
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                    ])
                    ->default('aktif')
                    ->required(),
                
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Urutan area dalam rute patroli'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_area')
            ->defaultSort('urutan', 'asc')
            ->columns([
                TextColumn::make('urutan')
                    ->label('No')
                    ->sortable()
                    ->alignCenter()
                    ->width(60),
                
                TextColumn::make('kode_area')
                    ->label('Kode Area')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('nama_area')
                    ->label('Nama Area')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Non-Aktif' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                
                TextColumn::make('patrols_count')
                    ->label('Jumlah Patroli')
                    ->counts('patrols')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Area Patroli')
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
            ])
            ->reorderable('urutan')
            ->emptyStateHeading('Belum ada area patroli')
            ->emptyStateDescription('Tambahkan area patroli untuk project ini.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }
}
