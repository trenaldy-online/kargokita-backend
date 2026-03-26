<?php

namespace App\Filament\Resources\RuteResource\Pages;

use App\Filament\Resources\RuteResource;
use App\Filament\Imports\RuteImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRutes extends ListRecords
{
    protected static string $resource = RuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(RuteImporter::class)
                ->label('Import CSV Harga')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\CreateAction::make(),
        ];
    }
}
