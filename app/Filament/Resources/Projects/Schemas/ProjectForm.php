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
                        Forms\Components\Toggle::make('enable_attendance_status')
                            ->label('Aktifkan Status Kehadiran')
                            ->helperText('Jika aktif, sistem akan otomatis menandai alpha untuk pegawai yang tidak hadir')
                            ->default(true),
                        
                        Forms\Components\Placeholder::make('shifts_info')
                            ->label('Kelola Shift')
                            ->content(fn ($record) => $record ? 'Kelola shift di menu Project Shifts' : 'Simpan project terlebih dahulu untuk mengelola shift')
                            ->visible(fn ($record) => true),
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
