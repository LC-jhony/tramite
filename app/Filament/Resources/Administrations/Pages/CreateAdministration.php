<?php

namespace App\Filament\Resources\Administrations\Pages;

use App\Filament\Resources\Administrations\AdministrationResource;
use App\Models\Administration;
use Filament\Resources\Pages\CreateRecord;

class CreateAdministration extends CreateRecord
{
    protected static string $resource = AdministrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
