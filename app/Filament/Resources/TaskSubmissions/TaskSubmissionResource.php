<?php

namespace App\Filament\Resources\TaskSubmissions;

use App\Filament\Resources\TaskSubmissions\Pages\ListTaskSubmissions;
use App\Filament\Resources\TaskSubmissions\Pages\ViewTaskSubmission;
use App\Filament\Resources\TaskSubmissions\Tables\TaskSubmissionsTable;
use App\Models\TaskSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskSubmissionResource extends Resource
{
    protected static ?string $model = TaskSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Checkpoint';

    protected static ?string $modelLabel = 'Checkpoint';

    protected static ?string $pluralModelLabel = 'Checkpoint';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return TaskSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaskSubmissions::route('/'),
            'view' => ViewTaskSubmission::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
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
