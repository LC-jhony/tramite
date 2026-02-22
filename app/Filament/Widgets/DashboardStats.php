<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalDocuments = Document::count();
        $pendingDocuments = Document::where('status', 'REGISTERED')->count();
        $inProcessDocuments = Document::where('status', 'IN_PROCESS')->count();
        $completedDocuments = Document::where('status', 'COMPLETED')->count();

        return [
            Stat::make('Total Documentos', number_format($totalDocuments))
                ->description('Todos los documentos')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('gray'),
            Stat::make('Pendientes', number_format($pendingDocuments))
                ->description('Documentos registrados')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('En Proceso', number_format($inProcessDocuments))
                ->description('Documentos en derivación')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('info'),
            Stat::make('Completados', number_format($completedDocuments))
                ->description('Documentos finalizados')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
