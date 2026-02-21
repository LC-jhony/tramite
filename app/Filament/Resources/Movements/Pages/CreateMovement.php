<?php

namespace App\Filament\Resources\Movements\Pages;

use App\Filament\Resources\Movements\MovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovement extends CreateRecord
{
    protected static string $resource = MovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
