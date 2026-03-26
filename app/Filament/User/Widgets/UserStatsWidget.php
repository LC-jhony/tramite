<?php

namespace App\Filament\User\Widgets;

use App\Models\Document;
use App\Models\Movement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $officeId = Auth::user()?->office_id;

        return [
            Stat::make('Pendientes', Document::where('status', 'en_proceso')
                ->when($officeId, fn ($q) => $q->where('current_office_id', $officeId))
                ->count())
                ->description('Documentos en proceso')
                ->color('warning'),

            Stat::make('Por Vencer', Document::whereDate('response_deadline', '<=', now()->addDays(3))
                ->when($officeId, fn ($q) => $q->where('current_office_id', $officeId))
                ->count())
                ->description('Deadline en 3 días')
                ->color('danger'),

            Stat::make('Recibidos Hoy', Movement::where('action', 'recibido')
                ->whereDate('created_at', today())
                ->when($officeId, fn ($q) => $q->where('to_office_id', $officeId))
                ->count())
                ->description('Movimientos recibidos')
                ->color('info'),

            Stat::make('Respondidos Hoy', Movement::where('action', 'respondido')
                ->whereDate('created_at', today())
                ->when($officeId, fn ($q) => $q->where('to_office_id', $officeId))
                ->count())
                ->description('Movimientos respondidos')
                ->color('success'),
        ];
    }
}
