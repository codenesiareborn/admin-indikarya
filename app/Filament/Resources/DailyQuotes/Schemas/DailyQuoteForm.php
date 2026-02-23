<?php

namespace App\Filament\Resources\DailyQuotes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DailyQuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->maxLength(255)
                    ->placeholder('Contoh: Semangat Pagi')
                    ->columnSpanFull(),
                
                Textarea::make('content')
                    ->label('Isi Kata Kata')
                    ->required()
                    ->rows(5)
                    ->maxLength(1000)
                    ->placeholder('Tulis kata-kata motivasi, inspirasi, atau quotes di sini...')
                    ->columnSpanFull(),
            ]);
    }
}
