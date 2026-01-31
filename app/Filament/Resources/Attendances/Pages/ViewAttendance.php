<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Detail Presensi';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pegawai')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('employee.nip')
                                    ->label('NIP'),
                                
                                TextEntry::make('employee.name')
                                    ->label('Nama Pegawai'),
                                
                                TextEntry::make('project.nama_project')
                                    ->label('Project'),
                            ]),
                    ]),
                
                Section::make('Informasi Presensi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tanggal')
                                    ->label('Tanggal')
                                    ->date('d F Y'),
                                
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'hadir' => 'success',
                                        'terlambat' => 'warning',
                                        'izin' => 'info',
                                        'sakit' => 'danger',
                                        'alpha' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                                
                                TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Tidak ada keterangan'),
                            ]),
                    ]),
                
                Section::make('Check In')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('check_in')
                                    ->label('Jam Masuk')
                                    ->time('H:i:s')
                                    ->placeholder('-'),
                                
                                TextEntry::make('check_in_location_link')
                                    ->label('Lokasi Check In')
                                    ->state(fn (Attendance $record) => 
                                        $record->check_in_latitude && $record->check_in_longitude
                                            ? 'Lihat di Google Maps'
                                            : '-'
                                    )
                                    ->url(fn (Attendance $record) => 
                                        $record->check_in_latitude && $record->check_in_longitude
                                            ? "https://www.google.com/maps?q={$record->check_in_latitude},{$record->check_in_longitude}"
                                            : null
                                    )
                                    ->openUrlInNewTab()
                                    ->color('success')
                                    ->icon('heroicon-o-map-pin'),
                            ]),
                        
                        ImageEntry::make('check_in_photo')
                            ->label('Foto Check In')
                            ->disk('public')
                            ->height(300)
                            ->defaultImageUrl(url('/images/no-image.png')),
                    ]),
                
                Section::make('Check Out')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('check_out')
                                    ->label('Jam Keluar')
                                    ->time('H:i:s')
                                    ->placeholder('-'),
                                
                                TextEntry::make('check_out_location_link')
                                    ->label('Lokasi Check Out')
                                    ->state(fn (Attendance $record) => 
                                        $record->check_out_latitude && $record->check_out_longitude
                                            ? 'Lihat di Google Maps'
                                            : '-'
                                    )
                                    ->url(fn (Attendance $record) => 
                                        $record->check_out_latitude && $record->check_out_longitude
                                            ? "https://www.google.com/maps?q={$record->check_out_latitude},{$record->check_out_longitude}"
                                            : null
                                    )
                                    ->openUrlInNewTab()
                                    ->color('success')
                                    ->icon('heroicon-o-map-pin'),
                            ]),
                        
                        ImageEntry::make('check_out_photo')
                            ->label('Foto Check Out')
                            ->disk('public')
                            ->height(300)
                            ->defaultImageUrl(url('/images/no-image.png')),
                    ]),
                
                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d F Y, H:i:s'),
                                
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d F Y, H:i:s'),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
