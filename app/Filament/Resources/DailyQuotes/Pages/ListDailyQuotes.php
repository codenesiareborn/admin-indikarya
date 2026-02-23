<?php

namespace App\Filament\Resources\DailyQuotes\Pages;

use App\Filament\Resources\DailyQuotes\DailyQuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyQuotes extends ListRecords
{
    protected static string $resource = DailyQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
