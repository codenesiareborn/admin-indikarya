<?php

namespace App\Filament\Resources\TaskLists;

use App\Filament\Resources\TaskLists\Pages\ListTaskLists;
use App\Filament\Resources\TaskLists\Pages\ManageProjectTaskList;
use App\Models\TaskList;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class TaskListResource extends Resource
{
    protected static ?string $model = TaskList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'nama_task';

    public static function getNavigationLabel(): string
    {
        return 'Task List';
    }

    public static function getModelLabel(): string
    {
        return 'Task List';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Task Lists';
    }

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_task')
                    ->label('Nama Task')
                    ->searchable(),
                TextColumn::make('room.nama_ruangan')
                    ->label('Ruangan'),
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaskLists::route('/'),
            'manage-project' => ManageProjectTaskList::route('/project'),
            'report' => \App\Filament\Resources\TaskLists\Pages\TaskListReportPage::route('/report'),
            'view-submission' => \App\Filament\Resources\TaskLists\Pages\ViewTaskSubmission::route('/submission/{record}'),
        ];
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
            return $query->whereHas('room.project', fn (Builder $q) => $q->whereIn('id', $projectIds));
        }
        
        return $query;
    }
}
