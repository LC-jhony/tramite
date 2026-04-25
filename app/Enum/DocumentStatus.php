<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum DocumentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Registrado = 'registrado';
    case EnProceso = 'en_proceso';
    case Respondido = 'respondido';
    case Finalizado = 'finalizado';
    case Rechazado = 'rechazado';
    case Cancelado = 'cancelado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Registrado => 'Registrado',
            self::EnProceso => 'En Proceso',
            self::Respondido => 'Respondido',
            self::Finalizado => 'Finalizado',
            self::Rechazado => 'Rechazado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Registrado => 'gray',
            self::EnProceso => 'info',
            self::Respondido => 'primary',
            self::Finalizado => 'success',
            self::Rechazado => 'danger',
            self::Cancelado => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Registrado => Heroicon::OutlinedDocument,
            self::EnProceso => Heroicon::OutlinedArrowPath,
            self::Respondido => Heroicon::OutlinedChatBubbleLeftRight,
            self::Finalizado => Heroicon::OutlinedCheckCircle,
            self::Rechazado => Heroicon::OutlinedXCircle,
            self::Cancelado => Heroicon::OutlinedArchive,
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Registrado => in_array($newStatus, [self::EnProceso, self::Cancelado]),
            self::EnProceso, self::Respondido => in_array($newStatus, [self::Finalizado, self::Rechazado, self::Cancelado]),
            self::Finalizado => false,
            self::Rechazado => false,
            self::Cancelado => false,
        };
    }
}
