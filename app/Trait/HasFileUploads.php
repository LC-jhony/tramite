<?php

declare(strict_types=1);

namespace App\Trait;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

trait HasFileUploads
{
    protected function syncFiles(array $paths): void
    {
        $existing = $this->record->documentFiles->pluck('path')->toArray();
        $toAdd = array_diff($paths, $existing);

        foreach ($toAdd as $path) {
            $mimeType = $this->getMimeType($path);
            $this->record->documentFiles()->create([
                'path' => $path,
                'original_name' => pathinfo($path, PATHINFO_BASENAME),
                'mime_type' => $mimeType,
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
            $mimeType = self::getMimeTypeStatic($path);
            $record->documentFiles()->create([
                'path' => $path,
                'original_name' => pathinfo($path, PATHINFO_BASENAME),
                'mime_type' => $mimeType,
            ]);
        }
    }

    public static function getUploadedPathsStatic(array $data, string $field = 'file_upload'): array
    {
        return array_filter((array) ($data[$field] ?? []));
    }

    protected function getMimeType(string $path): string
    {
        if (Storage::exists($path)) {
            return Storage::mimeType($path) ?? 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    protected static function getMimeTypeStatic(string $path): string
    {
        if (Storage::exists($path)) {
            return Storage::mimeType($path) ?? 'application/octet-stream';
        }

        return 'application/octet-stream';
    }
}
