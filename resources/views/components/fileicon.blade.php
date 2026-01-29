
@props(['file'])

@php
    use Illuminate\Support\Str;

    $record = $file ?? $getRecord();
    $extension = Str::lower(pathinfo($record->path ?? $record->filename, PATHINFO_EXTENSION));
    $icon = match($extension){
        'pdf' => 'bi-file-pdf-fill',
        'doc' => 'bi-file-word-fill',
        'docx' => 'bi-file-word-fill',
        'xls' => 'bi-file-excel-fill',
        'xlsx' => 'bi-file-excel-fill',
        'txt' => 'bi-file-text-fill',
        'jpg' => 'bi-file-image-fill',
        'jpeg' => 'bi-file-image-fill',
        'png' => 'bi-file-image-fill',
        'gif' => 'bi-file-image-fill',
        default => 'bi-file-fill'
    };
    $color = match($extension){
        'pdf' => 'text-red-600',
        'doc', 'docx' => 'text-blue-600',
        'xls', 'xlsx' => 'text-green-600',
        'jpg', 'jpeg', 'png', 'gif' => 'text-purple-600',
        'txt' => 'text-gray-600',
        default => 'text-gray-500'
    };
@endphp
<div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
    <x-filament::icon :icon="$icon" :class="$color . ' h-12 w-12 mb-2'" />
    <div class='text-center'>
        <p class='text-sm font-semibold text-gray-900 dark:text-white'>{{ strtoupper($extension) }}</p>
        <p class='text-xs text-gray-500 dark:text-gray-400'>{{ $record->created_at->format('d M Y') }}</p>
    </div>
</div>