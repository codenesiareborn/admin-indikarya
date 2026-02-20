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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Shift Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Morning Shift, Night Shift'),

                TextInput::make('code')
                    ->label('Shift Code')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('e.g., P, S, M')
                    ->helperText('Short code for this shift'),

                TimePicker::make('start_time')
                    ->label('Start Time')
                    ->required()
                    ->seconds(false),

                TimePicker::make('end_time')
                    ->label('End Time')
                    ->required()
                    ->seconds(false),

                CheckboxList::make('active_days')
                    ->label('Active Days')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->columns(4)
                    ->required()
                    ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),

                Toggle::make('is_overnight')
                    ->label('Overnight Shift')
                    ->default(false)
                    ->helperText('Enable for shifts that cross midnight (e.g., 22:00 - 06:00). Allows check-out on the next day.'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive shifts will not be available for selection'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Shift Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->sortable(),

                TextColumn::make('schedule_label')
                    ->label('Schedule')
                    ->sortable(),

                TextColumn::make('active_days_label')
                    ->label('Active Days')
                    ->wrap(),

                IconColumn::make('is_overnight')
                    ->label('Overnight')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_auto_generated')
                    ->label('Auto Generated')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Shift'),
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
            ->emptyStateHeading('No shifts found')
            ->emptyStateDescription('Add shifts to this project')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add Shift'),
            ]);
    }
}
