<?php

namespace App\Filament\Resources\Patrols;

use App\Filament\Resources\Patrols\Pages\ListPatrols;
use App\Filament\Resources\Patrols\Pages\ViewPatrol;
use App\Filament\Resources\Patrols\Pages\ManageProjectPatrol;
use App\Filament\Resources\Patrols\Tables\PatrolsTable;
use App\Models\Patrol;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatrolResource extends Resource
{
    protected static ?string $model = Patrol::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Patroli';

    protected static ?string $modelLabel = 'Patroli';

    protected static ?string $pluralModelLabel = 'Patroli';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return PatrolsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatrols::route('/'),
            'report' => \App\Filament\Resources\Patrols\Pages\PatrolReportPage::route('/report'),
            'manage-project' => ManageProjectPatrol::route('/project'),
            'view' => ViewPatrol::route('/{record}'),
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
