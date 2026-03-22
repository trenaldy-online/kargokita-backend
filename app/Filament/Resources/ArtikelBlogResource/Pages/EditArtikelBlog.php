<?php

namespace App\Filament\Resources\ArtikelBlogResource\Pages;

use App\Filament\Resources\ArtikelBlogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtikelBlog extends EditRecord
{
    protected static string $resource = ArtikelBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
