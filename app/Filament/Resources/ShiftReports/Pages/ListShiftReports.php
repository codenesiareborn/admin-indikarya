<?php

namespace App\Filament\Resources\ShiftReports\Pages;

use App\Filament\Resources\ShiftReports\ShiftReportResource;
use Filament\Resources\Pages\ListRecords;

class ListShiftReports extends ListRecords
{
    protected static string $resource = ShiftReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
