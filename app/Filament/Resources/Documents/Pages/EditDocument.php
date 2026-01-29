<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentFile;
use App\Models\Movement;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $files = $data['files'] ?? [];
        unset($data['files']);

        $record->update($data);
        // Handle file uploads after document is updated
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents', $filename, 'public');

                    DocumentFile::create([
                        'document_id' => $record->id,
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }
        }
        return $record;
    }
}
