<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use App\Trait\HasFileUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    use HasFileUploads;
    protected static string $resource = DocumentResource::class;
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['user_id'] = auth()->id();
    //     return $data;
    // }
    protected function afterCreate(): void
    {
        $this->syncFiles($this->getUploadedPaths('file_upload'));
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
