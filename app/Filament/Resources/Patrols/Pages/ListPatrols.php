<?php

namespace App\Filament\Resources\Patrols\Pages;

use App\Filament\Resources\Patrols\PatrolResource;
use Filament\Resources\Pages\ListRecords;

class ListPatrols extends ListRecords
{
    protected static string $resource = PatrolResource::class;

    protected static ?string $title = 'Daftar Patroli';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
