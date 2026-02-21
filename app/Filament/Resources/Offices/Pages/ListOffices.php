<?php

namespace App\Filament\Resources\Offices\Pages;

use App\Filament\Resources\Offices\OfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListOffices extends ListRecords
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }
}
