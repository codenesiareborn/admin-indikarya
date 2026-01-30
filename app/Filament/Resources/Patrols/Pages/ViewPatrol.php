<?php

namespace App\Filament\Resources\Patrols\Pages;

use App\Filament\Resources\Patrols\PatrolResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPatrol extends ViewRecord
{
    protected static string $resource = PatrolResource::class;

    protected static ?string $title = 'Detail Patroli';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Patroli')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('patrol_date')
                                    ->label('Tanggal Patroli')
                                    ->date('d F Y'),
                                
                                TextEntry::make('patrol_time')
                                    ->label('Waktu Patroli')
                                    ->time('H:i'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Nama Petugas'),
                                
                                TextEntry::make('project.nama_project')
                                    ->label('Project'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('area_name')
                                    ->label('Nama Area'),
                                
                                TextEntry::make('area_code')
                                    ->label('Kode Area'),
                            ]),
                        
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Aman' => 'success',
                                'Tidak Aman' => 'danger',
                                default => 'gray',
                            }),
                        
                        TextEntry::make('note')
                            ->label('Catatan')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Foto Patroli')
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('')
                            ->disk('public')
                            ->height(300)
                            ->defaultImageUrl(url('/images/no-image.png')),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('submitted_at')
                                    ->label('Dikirim Pada')
                                    ->dateTime('d F Y, H:i:s'),
                                
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d F Y, H:i:s'),
                            ]),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
