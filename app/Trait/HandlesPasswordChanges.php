<?php

namespace App\Trait;

trait HandlesPasswordChanges
{
    /**
     * Boot the trait and hook into password updates
     */
    public static function bootHandlesPasswordChanges(): void
    {
        // Actualizar password_changed_at cuando se cambia la contraseña
        static::updating(function ($user) {
            if ($user->isDirty('password')) {
                $user->password_changed_at = now();
            }
        });
    }

    /**
     * Verificar si la contraseña ha expirado
     */
    public function hasPasswordExpired(int $days = 90): bool
    {
        $passwordChangedAt = $this->password_changed_at ?? $this->created_at;

        return $passwordChangedAt->addDays($days)->isPast();
    }

    /**
     * Obtener días restantes hasta expiración
     */
    public function daysUntilPasswordExpires(int $days = 90): int
    {
        $passwordChangedAt = $this->password_changed_at ?? $this->created_at;
        $expiresAt = $passwordChangedAt->addDays($days);
        $now = now();

        return max(0, $now->diffInDays($expiresAt));
    }

    /**
     * Verificar si necesita cambiar contraseña pronto (7 días antes)
     */
    public function needsPasswordChangeSoon(int $days = 90, int $warningDays = 7): bool
    {
        return $this->daysUntilPasswordExpires($days) <= $warningDays;
    }
}
