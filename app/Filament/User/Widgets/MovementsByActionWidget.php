<?php

namespace App\Filament\User\Widgets;

use App\Enum\MovementAction;
use App\Models\Movement;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\FilaWidgets\Data\BreakdownItemData;
use LaravelDaily\FilaWidgets\Data\BreakdownWidgetData;
use LaravelDaily\FilaWidgets\Widgets\BreakdownWidget;

class MovementsByActionWidget extends BreakdownWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getWidgetLabel(): string
    {
        $officeName = Auth::user()?->office?->name;

        if ($officeName) {
            return "Movimientos por Acción - {$officeName}";
        }

        return 'Movimientos por Acción';
    }

    protected function getData(): BreakdownWidgetData
    {
        $officeId = Auth::user()?->office_id;

        $actions = MovementAction::cases();
        $items = [];

        foreach ($actions as $action) {
            $count = Movement::where('action', $action->value)
                ->when($officeId, fn ($q) => $q->where('to_office_id', $officeId))
                ->count();

            $items[] = new BreakdownItemData(
                label: $action->getLabel(),
                value: (float) $count,
                previousValue: null,
                color: $action->getColor(),
                icon: $action->getIcon(),
                url: null,
            );
        }

        return new BreakdownWidgetData(
            items: $items,
            description: 'Distribución de movimientos por tipo de acción',
        );
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
