<?php

namespace App\Filament\Resources\DailyQuotes\Pages;

use App\Filament\Resources\DailyQuotes\DailyQuoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyQuote extends EditRecord
{
    protected static string $resource = DailyQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
