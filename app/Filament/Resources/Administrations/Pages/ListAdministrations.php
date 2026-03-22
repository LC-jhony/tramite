<?php

namespace App\Filament\Resources\Administrations\Pages;

use App\Filament\Resources\Administrations\AdministrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAdministrations extends ListRecords
{
    protected static string $resource = AdministrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }
}
