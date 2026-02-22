<?php

namespace App\Filament\Widgets;

use App\Enum\DocumentStatus;
use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentsByStatus extends ChartWidget
{
    protected ?string $heading = 'Documentos por Estado';

    protected function getData(): array
    {
        $registered = Document::where('status', DocumentStatus::REGISTERED)->count();
        $inProcess = Document::where('status', DocumentStatus::IN_PROCESS)->count();
        $completed = Document::where('status', DocumentStatus::COMPLETED)->count();
        $rejected = Document::where('status', DocumentStatus::REJECTED)->count();
        $archived = Document::where('status', DocumentStatus::ARCHIVED)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Documentos',
                    'data' => [$registered, $inProcess, $completed, $rejected, $archived],
                    'backgroundColor' => [
                        '#f59e0b',
                        '#3b82f6',
                        '#10b981',
                        '#ef4444',
                        '#6b7280',
                    ],
                ],
            ],
            'labels' => ['Registrados', 'En Proceso', 'Completados', 'Rechazados', 'Archivados'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
