<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Project')
                    ->schema([
                        Forms\Components\TextInput::make('nama_project')
                            ->label('Nama Project')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('jenis_project')
                            ->label('Jenis Project')
                            ->options([
                                'cleaning_services' => 'Cleaning Services',
                                'security_services' => 'Security Services',
                            ])
                            ->required(),
                        
                        Forms\Components\Textarea::make('alamat_lengkap')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3),
                        
                        Forms\Components\TextInput::make('nilai_kontrak')
                            ->label('Nilai Kontrak (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => str_replace('.', '', $state)),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->native(false),
                                
                                Forms\Components\DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->native(false)
                                    ->afterOrEqual('tanggal_mulai'),
                            ]),
                    ]),
                
                Section::make('Pengaturan Presensi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('jam_masuk')
                                    ->label('Jam Masuk')
                                    ->required()
                                    ->seconds(false),
                                
                                Forms\Components\TimePicker::make('jam_keluar')
                                    ->label('Jam Keluar')
                                    ->required()
                                    ->seconds(false)
                                    ->after('jam_masuk'),
                            ]),
                    ]),
                
                Section::make('Status Project')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Project')
                            ->options([
                                'draft' => 'Draft',
                                'aktif' => 'Aktif',
                                'selesai' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }
}
