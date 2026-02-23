<?php

namespace App\Filament\Resources\DailyQuotes;

use App\Filament\Resources\DailyQuotes\Pages\CreateDailyQuote;
use App\Filament\Resources\DailyQuotes\Pages\EditDailyQuote;
use App\Filament\Resources\DailyQuotes\Pages\ListDailyQuotes;
use App\Filament\Resources\DailyQuotes\Schemas\DailyQuoteForm;
use App\Filament\Resources\DailyQuotes\Tables\DailyQuotesTable;
use App\Models\DailyQuote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyQuoteResource extends Resource
{
    protected static ?string $model = DailyQuote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kata Kata Hari Ini';
    }

    public static function getModelLabel(): string
    {
        return 'Kata Kata';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kata Kata Hari Ini';
    }

    public static function form(Schema $schema): Schema
    {
        return DailyQuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyQuotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyQuotes::route('/'),
            'create' => CreateDailyQuote::route('/create'),
            'edit' => EditDailyQuote::route('/{record}/edit'),
        ];
    }
}
