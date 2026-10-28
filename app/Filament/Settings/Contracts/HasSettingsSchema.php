<?php

namespace App\Filament\Settings\Contracts;

use Filament\Schemas\Components\Component;

interface HasSettingsSchema
{
    /** Identificador único del grupo (debe coincidir con la columna `group`). */
    public function group(): string;

    /**
     * Pestaña del panel donde se muestra este grupo.
     *
     * Por defecto cada grupo tiene su propia pestaña. Devuelve el `group()`
     * de otro esquema (ej. `'general'`) para mostrar varios grupos juntos
     * dentro de una sola pestaña: cada uno conserva su propio `statePath`,
     * así que el almacenamiento y el bloqueo por sección no cambian.
     */
    public function tab(): string;

    /** Etiqueta visible en el tab del panel. */
    public function label(): string;

    /** Icono heroicon del tab. */
    public function icon(): string;

    /** Orden de aparición del tab (menor = primero). */
    public function sort(): int;

    /** Número de columnas del formulario dentro del tab. */
    public function columns(): int;

    /**
     * Componentes del formulario de Filament (TextInput, Toggle, etc).
     *
     * @return array<int, Component>
     */
    public function schema(): array;
}
