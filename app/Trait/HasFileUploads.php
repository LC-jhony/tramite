<?php

namespace App\Trait;

use App\Models\Document;

trait HasFileUploads
{
    protected function syncFiles(array $paths): void
    {
        $existing = $this->record->documentFiles->pluck('path')->toArray();
        $toAdd = array_diff($paths, $existing);

        foreach ($toAdd as $path) {
            $this->record->documentFiles()->create([
                'path' => $path,
                'original_name' => pathinfo($path, PATHINFO_BASENAME),
                'mime_type' => 'application/octet-stream',
            ]);
        }
    }

    protected function getUploadedPaths(string $field = 'file_upload'): array
    {
        return array_filter((array) ($this->data[$field] ?? []));
    }

    public static function syncFilesStatic(Document $record, array $paths): void
    {
        $existing = $record->documentFiles->pluck('path')->toArray();
        $toAdd = array_diff($paths, $existing);

        foreach ($toAdd as $path) {
            $record->documentFiles()->create([
                'path' => $path,
                'original_name' => pathinfo($path, PATHINFO_BASENAME),
                'mime_type' => 'application/octet-stream',
            ]);
        }
    }

    public static function getUploadedPathsStatic(array $data, string $field = 'file_upload'): array
    {
        return array_filter((array) ($data[$field] ?? []));
    }
}
