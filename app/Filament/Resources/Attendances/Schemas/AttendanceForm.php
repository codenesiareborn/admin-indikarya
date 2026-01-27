<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Employee')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'id')
                    ->required(),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('check_in'),
                TextInput::make('check_in_photo')
                    ->default(null),
                TextInput::make('check_in_latitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('check_in_longitude')
                    ->numeric()
                    ->default(null),
                TimePicker::make('check_out'),
                TextInput::make('check_out_photo')
                    ->default(null),
                TextInput::make('check_out_latitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('check_out_longitude')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options([
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
        ])
                    ->default('alpha')
                    ->required(),
                Textarea::make('keterangan')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
