<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShiftsRelationManager extends RelationManager
{
    protected static string $relationship = 'shifts';

    protected static ?string $title = 'Shifts';

    protected static ?string $recordTitleAttribute = 'name';

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
                TextInput::make('name')
                    ->label('Nama Shift')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('contoh: Shift Pagi, Shift Malam'),

                TextInput::make('code')
                    ->label('Kode Shift')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('contoh: P, S, M')
                    ->helperText('Kode singkat untuk shift ini'),

                TimePicker::make('start_time')
                    ->label('Waktu Mulai')
                    ->required()
                    ->seconds(false),

                TimePicker::make('end_time')
                    ->label('Waktu Selesai')
                    ->required()
                    ->seconds(false),

                CheckboxList::make('active_days')
                    ->label('Hari Aktif')
                    ->options([
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                        'saturday' => 'Sabtu',
                        'sunday' => 'Minggu',
                    ])
                    ->columns(4)
                    ->required()
                    ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),

                Toggle::make('is_overnight')
                    ->label('Shift Malam')
                    ->default(false)
                    ->helperText('Aktifkan untuk shift yang melewati tengah malam (contoh: 22:00 - 06:00). Memungkinkan check-out pada hari berikutnya.'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Shift yang tidak aktif tidak akan tersedia untuk dipilih'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Shift')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->sortable(),

                TextColumn::make('schedule_label')
                    ->label('Jadwal')
                    ->sortable(),

                TextColumn::make('active_days_label')
                    ->label('Hari Aktif')
                    ->wrap(),

                IconColumn::make('is_overnight')
                    ->label('Malam')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_auto_generated')
                    ->label('Otomatis')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Shift'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada shift ditemukan')
            ->emptyStateDescription('Tambah shift untuk project ini')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tambah Shift'),
            ]);
    }
}
