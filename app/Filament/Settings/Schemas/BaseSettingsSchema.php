<?php

namespace App\Filament\Settings\Schemas;

use App\Filament\Settings\Contracts\HasSettingsSchema;

abstract class BaseSettingsSchema implements HasSettingsSchema
{
    protected int $sort = 0;

    protected int $columns = 1;

    public function sort(): int
    {
        return $this->sort;
    }

    /**
     * Una pestaña por esquema. Una subclase puede sobrescribirlo para
     * mostrar su grupo dentro de la pestaña de otro esquema.
     */
    public function tab(): string
    {
        return $this->group();
    }

    public function columns(): int
    {
        return $this->columns;
    }
}
