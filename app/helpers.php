<?php

use App\Settings\Contracts\SettingsRepository;

if (! function_exists('setting')) {
    /**
     * Lee un valor de configuración desde cualquier parte de la app.
     *
     *   setting('social')                  // array completo del grupo "social"
     *   setting('social.facebook')         // valor puntual (dot notation)
     *   setting('social.facebook', '#')    // con valor por defecto
     */
    function setting(string $key, mixed $default = null): mixed
    {
        [$group, $path] = array_pad(explode('.', $key, 2), 2, null);

        return app(SettingsRepository::class)->get($group, $path, $default);
    }
}

if (! function_exists('settings_repository')) {
    /**
     * Acceso directo al repositorio para operaciones de escritura,
     * bloqueo, etc. (uso típico en comandos, jobs o tests).
     */
    function settings_repository(): SettingsRepository
    {
        return app(SettingsRepository::class);
    }
}
