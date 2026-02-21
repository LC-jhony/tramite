<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
