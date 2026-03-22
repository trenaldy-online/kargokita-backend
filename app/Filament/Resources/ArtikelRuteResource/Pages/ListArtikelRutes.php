<?php

namespace App\Filament\Resources\ArtikelRuteResource\Pages;

use App\Filament\Resources\ArtikelRuteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtikelRutes extends ListRecords
{
    protected static string $resource = ArtikelRuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
