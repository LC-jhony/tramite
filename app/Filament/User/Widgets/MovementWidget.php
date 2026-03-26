<?php

namespace App\Filament\User\Widgets;

use App\Enum\MovementAction;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\FilaWidgets\Widgets\CompletionRateWidget;

class MovementWidget extends CompletionRateWidget
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
            return "Movimiento del Documento - {$officeName}";
        }

        return 'Movimiento del Documento';
    }

    protected function getCounts(): array
    {
        $officeId = Auth::user()?->office_id;

        $total = Movement::query()
            ->when($officeId, fn ($q) => $q->where('to_office_id', $officeId))
            ->count();

        $completedActions = [MovementAction::Respondido, MovementAction::Derivado];
        $completedValues = array_column($completedActions, 'value');

        $completed = Movement::whereIn('action', $completedValues)
            ->when($officeId, fn ($q) => $q->where('to_office_id', $officeId))
            ->count();

        return [
            'completed' => $completed,
            'total' => $total,
        ];
    }

    protected function getWidgetFormat(): string
    {
        return 'number';
    }

    protected function getWidgetPrecision(): int
    {
        return 0;
    }
}
