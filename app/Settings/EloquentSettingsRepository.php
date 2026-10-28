<?php

namespace App\Settings;

use App\Models\Setting;
use App\Settings\Contracts\SettingsRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class EloquentSettingsRepository implements SettingsRepository
{
    public function __construct(
        protected readonly string $settingName,
        protected readonly bool $cacheEnabled,
        protected readonly ?string $cacheStore,
        protected readonly string $cachePrefix,
    ) {}

    public function get(string $group, ?string $key = null, mixed $default = null): mixed
    {
        $payload = $this->all($group);

        if ($key === null) {
            return $payload !== [] ? $payload : $default;
        }

        return Arr::get($payload, $key, $default);
    }

    public function all(string $group): array
    {
        if (! $this->cacheEnabled) {
            return $this->fetch($group);
        }

        return $this->cacheRepository()->rememberForever(
            $this->cacheKey($group),
            fn () => $this->fetch($group),
        );
    }

    public function put(string $group, array $payload): void
    {
        $this->guardAgainstLocked($group);

        Setting::query()->updateOrCreate(
            ['group' => $group, 'name' => $this->settingName],
            ['payload' => $payload],
        );

        $this->forget($group);
    }

    public function merge(string $group, array $partialPayload): void
    {
        // `put()` ya ejecuta guardAgainstLocked(), así que no hay que
        // validarlo dos veces. `fetch()` lee de BD sin caché a propósito:
        // nunca se debe mezclar con un payload potencialmente viejo.
        $this->put($group, array_replace($this->fetch($group), $partialPayload));
    }

    public function lock(string $group): void
    {
        Setting::query()->updateOrCreate(
            ['group' => $group, 'name' => $this->settingName],
            ['locked' => true],
        );

        $this->forget($group);
    }

    public function unlock(string $group): void
    {
        Setting::query()
            ->where('group', $group)
            ->where('name', $this->settingName)
            ->update(['locked' => false]);

        $this->forget($group);
    }

    public function isLocked(string $group): bool
    {
        return (bool) Setting::query()
            ->where('group', $group)
            ->where('name', $this->settingName)
            ->value('locked');
    }

    public function forget(string $group): void
    {
        $this->cacheRepository()->forget($this->cacheKey($group));
    }

    public function flush(): void
    {
        // Recorre los grupos que existen en la tabla. NO lee
        // config('settings.schemas'): la capa App\Settings no debe
        // conocer a la capa Filament (regla de oro de §1), y así además
        // se limpian los grupos que ya no tengan schema registrado.
        foreach (Setting::query()->distinct()->pluck('group') as $group) {
            $this->forget((string) $group);
        }
    }

    protected function guardAgainstLocked(string $group): void
    {
        if ($this->isLocked($group)) {
            throw new RuntimeException(
                "La sección de configuración '{$group}' está bloqueada y no puede modificarse."
            );
        }
    }

    protected function fetch(string $group): array
    {
        $setting = Setting::query()
            ->where('group', $group)
            ->where('name', $this->settingName)
            ->first(['payload']);

        return $setting?->payload ?? [];
    }

    protected function cacheKey(string $group): string
    {
        return "{$this->cachePrefix}:{$group}";
    }

    protected function cacheRepository(): CacheRepository
    {
        return $this->cacheStore ? Cache::store($this->cacheStore) : Cache::store();
    }
}
