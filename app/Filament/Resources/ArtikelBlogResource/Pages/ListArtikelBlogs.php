<?php

namespace App\Filament\Resources\ArtikelBlogResource\Pages;

use App\Filament\Resources\ArtikelBlogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtikelBlogs extends ListRecords
{
    protected static string $resource = ArtikelBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
