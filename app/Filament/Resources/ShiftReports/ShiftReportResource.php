<?php

namespace App\Filament\Resources\ShiftReports;

use App\Filament\Resources\ShiftReports\Pages\ListShiftReports;
use App\Filament\Resources\ShiftReports\Pages\ShiftReportPage;
use App\Filament\Resources\ShiftReports\Tables\ShiftReportsTable;
use App\Models\ShiftReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ShiftReportResource extends Resource
{
    protected static ?string $model = ShiftReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Laporan Shift';

    protected static ?string $modelLabel = 'Laporan Shift';

    protected static ?string $pluralModelLabel = 'Laporan Shift';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Personil')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('project.nama_project')
                    ->label('Project')
                    ->sortable(),
                
                TextColumn::make('shift_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('shift_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('report')
                    ->label('Laporan')
                    ->limit(50)
                    ->tooltip(fn (ShiftReport $record): string => $record->report),

                TextColumn::make('submitted_at')
                    ->label('Waktu Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('shift_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShiftReports::route('/'),
            'report' => ShiftReportPage::route('/report'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        if ($user && ($user->hasRole('super_admin') || $user->hasRole('admin'))) {
            return $query;
        }
        
        if ($user && $user->isPic()) {
            $projectIds = $user->getPicProjectIds();
            return $query->whereIn('project_id', $projectIds);
        }
        
        return $query;
    }
}
