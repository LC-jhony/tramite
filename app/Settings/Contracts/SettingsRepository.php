<?php

namespace App\Settings\Contracts;

interface SettingsRepository
{
    public function get(string $group, ?string $key = null, mixed $default = null): mixed;

    public function all(string $group): array;

    public function put(string $group, array $payload): void;

    public function merge(string $group, array $partialPayload): void;

    public function lock(string $group): void;

    public function unlock(string $group): void;

    public function isLocked(string $group): bool;

    public function forget(string $group): void;

    public function flush(): void;
}
