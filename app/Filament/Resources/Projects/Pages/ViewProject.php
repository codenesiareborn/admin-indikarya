<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Deskripsi Project')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nama_project')
                                    ->label('Nama Project')
                                    ->weight(FontWeight::Bold),
                                
                                TextEntry::make('jenis_project_label')
                                    ->label('Jenis Project')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Cleaning Services' => 'success',
                                        'Security Services' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status_label')
                                    ->label('Status Project')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Draft' => 'gray',
                                        'Aktif' => 'success',
                                        'Selesai' => 'primary',
                                        default => 'gray',
                                    }),
                                
                                TextEntry::make('alamat_lengkap')
                                    ->label('Alamat Lengkap'),
                            ]),
                        
                        TextEntry::make('nilai_kontrak')
                            ->label('Nilai Kontrak')
                            ->money('IDR')
                            ->weight(FontWeight::Bold),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->date('d F Y'),
                                
                                TextEntry::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->date('d F Y'),
                            ]),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Pengaturan Absensi')
                    ->schema([
                        TextEntry::make('jam_masuk')
                            ->label('Jam Masuk')
                            ->time('H:i'),
                        
                        TextEntry::make('jam_keluar')
                            ->label('Jam Keluar')
                            ->time('H:i'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                
                Section::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i'),
                        
                        TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(1)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
