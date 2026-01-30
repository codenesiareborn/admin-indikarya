<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.'),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('staf')
                                    ->label('Staf')
                                    ->options([
                                        'cleaning_services' => 'Cleaning Services',
                                        'security_services' => 'Security Services',
                                    ])
                                    ->required(),
                                
                                Forms\Components\Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'laki-laki' => 'Laki-laki',
                                        'perempuan' => 'Perempuan',
                                    ])
                                    ->required(),
                            ]),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('no_hp')
                            ->label('No HP')
                            ->tel()
                            ->maxLength(20),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->required()
                                    ->native(false)
                                    ->maxDate(now())
                                    ->displayFormat('d/m/Y'),
                                
                                Forms\Components\DatePicker::make('tanggal_masuk')
                                    ->label('Tanggal Masuk Perusahaan')
                                    ->required()
                                    ->native(false)
                                    ->default(now())
                                    ->displayFormat('d/m/Y'),
                            ]),
                        
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->rows(3),
                    ]),
                
                Section::make('Status Pegawai')
                    ->schema([
                        Forms\Components\Select::make('status_pegawai')
                            ->label('Status Pegawai')
                            ->options([
                                'aktif' => 'Aktif',
                                'non-aktif' => 'Non-Aktif',
                                'cuti' => 'Cuti',
                            ])
                            ->default('aktif')
                            ->required(),
                        
                        Forms\Components\Hidden::make('role')
                            ->default('employee'),
                    ]),
            ]);
    }
}
