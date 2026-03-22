<?php

namespace App\Filament\Resources\Administrations\Pages;

use App\Filament\Resources\Administrations\AdministrationResource;
use App\Models\Administration;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdministration extends EditRecord
{
    protected static string $resource = AdministrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Administration::where('status', 1)
            ->where('id', '!=', $this->record->id)
            ->update(['status' => 0]);

        if (! empty($data['start_period']) && is_numeric($data['start_period'])) {
            $endYear = (int) $data['start_period'] + 3;
            $data['end_period'] = (string) $endYear;
            $data['name'] = 'Gestión '.$data['start_period'].' - '.$endYear;
        }

        return $data;
    }
}
