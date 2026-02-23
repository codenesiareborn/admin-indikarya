<?php

namespace App\Filament\Resources\TaskSubmissions\Pages;

use App\Filament\Resources\TaskSubmissions\TaskSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListTaskSubmissions extends ListRecords
{
    protected static string $resource = TaskSubmissionResource::class;

    protected static ?string $title = 'Daftar Checkpoint';
}
