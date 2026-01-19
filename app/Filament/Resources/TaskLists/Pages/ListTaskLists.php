<?php

namespace App\Filament\Resources\TaskLists\Pages;

use App\Filament\Resources\TaskLists\TaskListResource;
use Filament\Resources\Pages\ListRecords;

class ListTaskLists extends ListRecords
{
    protected static string $resource = TaskListResource::class;
}
