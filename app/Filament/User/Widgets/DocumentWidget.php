<?php

namespace App\Filament\User\Widgets;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\FilaWidgets\Data\HeatmapCalendarWidgetData;
use LaravelDaily\FilaWidgets\Widgets\HeatmapCalendarWidget;

class DocumentWidget extends HeatmapCalendarWidget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getWidgetLabel(): string
    {
        $officeName = Auth::user()?->office?->name;

        if ($officeName) {
            return "Documentos Registrados - {$officeName}";
        }

        return 'Documentos Registrados';
    }

    protected function getData(): HeatmapCalendarWidgetData
    {
        $start = now()->startOfYear();
        $end = now()->endOfYear();
        $officeId = Auth::user()?->office_id;

        $query = Document::whereBetween('created_at', [$start, $end])
            ->when($officeId, fn ($q) => $q->where('current_office_id', $officeId));

        $documents = $query
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->all();

        $entryUrls = [];
        foreach (array_keys($documents) as $date) {
            $entryUrls[$date] = route('filament.user.resources.documents.index', [
                'tableFilters' => ['created_at' => ['date' => $date]],
            ]);
        }

        return new HeatmapCalendarWidgetData(
            entries: $documents,
            description: 'Documentos creados este año',
            entryUrls: $entryUrls,
            openEntryUrlsInNewTab: true,
        );

    }

    protected function getWidgetFormat(): string
    {
        return 'number';
    }

    protected function getWeeksToShow(): int
    {
        return 24;
    }
}
