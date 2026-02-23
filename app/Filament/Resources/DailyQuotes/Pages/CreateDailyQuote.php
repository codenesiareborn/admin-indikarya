<?php

namespace App\Filament\Resources\DailyQuotes\Pages;

use App\Filament\Resources\DailyQuotes\DailyQuoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyQuote extends CreateRecord
{
    protected static string $resource = DailyQuoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}
