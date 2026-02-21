<?php

namespace App\Filament\User\Resources\Movements\Pages;

use App\Filament\User\Resources\Movements\MovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovement extends CreateRecord
{
    protected static string $resource = MovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
