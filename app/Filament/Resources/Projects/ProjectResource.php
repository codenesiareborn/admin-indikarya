<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_project';

    public static function getNavigationLabel(): string
    {
        return 'Management Project';
    }

    public static function getModelLabel(): string
    {
        return 'Project';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Projects';
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RoomsRelationManager::class,
            RelationManagers\TasksRelationManager::class,
            RelationManagers\EmployeesRelationManager::class,
            RelationManagers\PatrolAreasRelationManager::class,
            RelationManagers\PicsRelationManager::class,
            RelationManagers\ShiftsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        // If user is super_admin or admin, show all projects
        if ($user && ($user->hasRole('super_admin') || $user->hasRole('admin'))) {
            return $query;
        }
        
        // If user is PIC, filter to only assigned projects
        if ($user && $user->isPic()) {
            return $query->whereHas('pics', fn (Builder $q) => $q->where('user_id', $user->id));
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
