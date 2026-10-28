<?php

namespace App\Filament\Settings;

use App\Filament\Settings\Contracts\HasSettingsSchema;
use Illuminate\Support\Collection;

class SettingsSchemaRegistry
{
    /** @var Collection<int, HasSettingsSchema> */
    protected Collection $schemas;

    public function __construct()
    {
        $this->schemas = collect(config('settings.schemas', []))
            ->map(fn (string $class) => app($class))
            ->sortBy(fn (HasSettingsSchema $schema) => $schema->sort())
            ->values();
    }

    /** @return Collection<int, HasSettingsSchema> */
    public function all(): Collection
    {
        return $this->schemas;
    }

    /**
     * Esquemas agrupados por la pestaña a la que pertenecen, preservando el
     * orden declarado en cada `sort()`. La clave de cada grupo es el
     * `group()` del esquema dueño de la pestaña.
     *
     * @return Collection<string, Collection<int, HasSettingsSchema>>
     */
    public function byTab(): Collection
    {
        return $this->schemas
            ->groupBy(fn (HasSettingsSchema $schema) => $schema->tab())
            ->map(fn (Collection $schemas) => $schemas->values());
    }

    public function find(string $group): ?HasSettingsSchema
    {
        return $this->schemas->first(
            fn (HasSettingsSchema $schema) => $schema->group() === $group
        );
    }

    /** @return array<int, string> */
    public function groups(): array
    {
        return $this->schemas
            ->map(fn (HasSettingsSchema $schema) => $schema->group())
            ->all();
    }
}
