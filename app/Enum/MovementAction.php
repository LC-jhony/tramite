<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MovementAction: string implements HasColor, HasIcon, HasLabel
{
    case Derivado = 'derivado';
    case Recibido = 'recibido';
    case Respondido = 'respondido';
    case Rechazado = 'rechazado';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Derivado => 'Derivado',
            self::Recibido => 'Recibido',
            self::Respondido => 'Respondido',
            self::Rechazado => 'Rechazado',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Derivado => 'info',
            self::Recibido => 'primary',
            self::Respondido => 'success',
            self::Rechazado => 'danger',
            self::Finalizado => 'success',
            self::Cancelado => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Derivado => 'heroicon-o-paper-airplane',
            self::Recibido => 'heroicon-o-inbox-arrow-down',
            self::Respondido => 'heroicon-o-chat-bubble-left-right',
            self::Rechazado => 'heroicon-o-no-symbol',
            self::Finalizado => 'heroicon-o-check-badge',
            self::Cancelado => 'heroicon-o-x-circle',
        };
    }
}
