<?php

namespace App\Filament\User\Resources\Movements\Pages;

use App\Filament\User\Resources\Movements\MovementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMovement extends EditRecord
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
