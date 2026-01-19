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
                TextColumn::make('employee.id')
                    ->searchable(),
                TextColumn::make('project.id')
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->time()
                    ->sortable(),
                TextColumn::make('check_in_photo')
                    ->searchable(),
                TextColumn::make('check_in_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->time()
                    ->sortable(),
                TextColumn::make('check_out_photo')
                    ->searchable(),
                TextColumn::make('check_out_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_out_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
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
