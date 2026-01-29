<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\DocumentFile;
use App\Models\Movement;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $files = $data['files'] ?? [];
        unset($data['files']);

        $document = static::getModel()::create($data);

        // Handle file uploads after document is created
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents', $filename, 'public');

                    DocumentFile::create([
                        'document_id' => $document->id,
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }
        }
        return $document;
    }
}
