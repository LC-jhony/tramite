<?php

use App\Filament\Settings\Schemas\ColorThemeSettingsSchema;
use App\Filament\Settings\Schemas\GeneralSettingsSchema;
use App\Filament\Settings\Schemas\LegalSettingsSchema;
use App\Filament\Settings\Schemas\SocialSettingsSchema;

return [

    /*
    |--------------------------------------------------------------------------
    | Schemas de configuración registrados
    |--------------------------------------------------------------------------
    |
    | Cada clase debe implementar App\Filament\Settings\Contracts\HasSettingsSchema.
    | Para agregar una nueva sección (grupo) de configuración al panel:
    |
    |   1. Crea una clase en app/Filament/Settings/Schemas/ que extienda
    |      BaseSettingsSchema.
    |   2. Regístrala en el array de abajo.
    |
    | No es necesario tocar la página de Filament ni el repositorio: el tab,
    | el formulario y el almacenamiento se generan automáticamente.
    |
    */
    'schemas' => [
        GeneralSettingsSchema::class,
        SocialSettingsSchema::class,
        LegalSettingsSchema::class,
        ColorThemeSettingsSchema::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombre fijo de fila por grupo
    |--------------------------------------------------------------------------
    |
    | Tu tabla `settings` tiene unique(group, name). Guardamos TODO el payload
    | de cada sección en una sola fila por grupo, usando este `name` constante.
    | Esto mantiene la tabla pequeña y las lecturas/escrituras simples.
    |
    */
    'setting_name' => 'config',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'store' => env('SETTINGS_CACHE_STORE'), // null = store por defecto
        'prefix' => 'settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Disco donde viven los archivos subidos desde los settings
    |--------------------------------------------------------------------------
    |
    | Debe ser un disco PÚBLICO: el preview en vivo (§10) y el sitio web
    | leen estas URLs desde fuera de la app. Si cambias esto, cambia también
    | ->disk() en los FileUpload de §7.3 / §7.6.
    |
    */
    'upload_disk' => env('SETTINGS_UPLOAD_DISK', 'public'),

];
