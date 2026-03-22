<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use App\Trait\HasFileUploads;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    use HasFileUploads;
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
        {
            $data['file_upload'] = $this->record->documentFiles->pluck('path')->toArray();

            return $data;
        }
        protected function afterSave(): void
        {
            $this->syncFiles($this->getUploadedPaths('file_upload'));
        }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
