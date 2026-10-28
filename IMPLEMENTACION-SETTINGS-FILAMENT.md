# Sistema de Configuración (Settings) para Filament v5

Guía completa, con **todo el código**, para implementar un panel de
configuración — **4 pestañas**: `General`, `Redes Sociales`,
`Términos y Condiciones` y `Tema del panel` — en Filament v5, con:

- Arquitectura desacoplada y escalable (agregar una sección nueva = crear
  una clase, sin tocar la página; agrupar varias en la misma pestaña = un
  método más en esa clase, ver §7.1 — hoy ningún grupo comparte pestaña,
  pero el mecanismo sigue vivo en `form()`).
- **Pestañas `SEO` y `Mantenimiento` fuera del panel desde el 2026-10-11**
  (§7.6 / §7.7): sus clases siguen en el proyecto pero ya **no están
  registradas** en `config/settings.php`, así que no aparecen en el panel.
  Los valores guardados (`seo.*`, `maintenance.*`) **siguen leyéndose** en
  la web pública (`partials/seo-head.blade.php`) y en el middleware de
  mantenimiento; lo que ya no hay es UI para editarlos.
- Cache automática con invalidación en cada escritura.
- Bloqueo de secciones (`locked`) desde la propia UI.
- Panel de **preview en vivo** (dos columnas) que refleja los cambios de
  "General" sin necesidad de guardar: tarjeta nativa (componentes de
  Filament, clases `fi-sc-*`); sólo el layout de la página necesita el
  theme de §15.
- Helper global `setting()` para leer configuración desde cualquier parte
  de la app (Blade, controllers, jobs, middleware).
- Selector del **color primario del panel** (§16, `ColorThemeSettingsSchema`):
  las 26 paletas nativas de `Filament\Support\Colors\Color`, con rejilla de
  swatches que las muestra con su nombre, guardadas en `settings` y aplicadas
  al panel **sin migración nueva**.
- Tests con Pest.

> **Entorno sobre el que está escrita esta guía (verificado):**
>
> | Componente | Versión / valor real |
> |---|---|
> | PHP | 8.5 |
> | Laravel | 13.33 |
> | Filament | `^5.8` (v5.8.4) — **ya instalado** |
> | Livewire | 4.x (requisito de Filament 5) |
> | Tailwind CSS | 4.3.3 — **CSS-first, no existe `tailwind.config.js`** |
> | Vite | 8 |
> | Base de datos | **MariaDB 11.8** — `127.0.0.1`, BD `laravel` (`.env`). *El `.env.example` trae sqlite, pero **ese no es** el entorno real de esta máquina*. **Ojo: hay un segundo esquema `tramite` en el mismo servidor** (lo usan `tramite_01`/`tramite_02` y su tabla `settings` está vacía) — si un cliente MySQL "no ve nada", está mirando ese |
> | Cache store | `database` (`create_cache_table` ya existe) |
> | Panel | `AdminPanelProvider` → id `admin`, ruta **`/admin`** |
> | Disco de subida | `FILESYSTEM_DISK=local` → **privado** (ver §2.2) |
> | Tests | `phpunit.xml` fuerza sqlite `:memory:` y **esta PHP no tiene `pdo_sqlite`** → hoy todo test con BD falla con `could not find driver` (§13) |
>
> **APIs de Filament v5 verificadas** contra el código fuente de
> `filamentphp/filament` (rama `5.x`): `Filament\Schemas\Schema` como
> tipo del `form()`, `Tabs::tabs()` / `persistTabInQueryString()`,
> `Tab::disabled()`, `Action::schema()` (el `form()` está deprecado),
> `getHeaderActions()`, `DatePicker::native()`,
> `FileUpload::imageEditor()`. **`Filament\Pages\Page` ya implementa
> `HasSchemas`**, así que no hace falta ningún trait extra en la página.

---

## Estado de la implementación (actualizado 2026-10-11)

**El código de §2 a §12, de §16, §19 y §20 ya está en el proyecto.** Verificado en este
mismo
entorno: la app arranca (`php artisan route:list`), la ruta
`admin/manage-settings` existe, `errors.maintenance` compila y renderiza, y
el modo mantenimiento se probó contra el kernel HTTP real:

| Escenario | Resultado |
|---|---|
| Mantenimiento OFF → `GET /` | 200 ✅ |
| Mantenimiento ON → `GET /` | 503 con la vista ✅ |
| Mantenimiento ON → `GET /admin` | 302 (login) — no se bloquea a uno mismo ✅ |
| Mantenimiento ON → `GET /up` | 200 ✅ |

> **Guardado verificado end-to-end (2026-09-26).** `GET /admin/manage-settings`
> con sesión devuelve 200 y trae el `wire:submit="save"` con el botón
> *Guardar cambios*. Y el mismo camino que ejecuta ese botón,
> `Livewire::test(ManageSettings::class)->set('data.general.site_name', …)->call('save')`,
> escribió de verdad: `settings.updated_at = 2026-09-26 11:53:26` y
> `setting('general.site_name')` devolvió el valor nuevo con la cache ya
> invalidada. El valor original se restauró después de la prueba.
>
> Detalle que confunde mucho: **`updated_at` sólo cambia si el `payload`
> cambió.** Si guardas sin modificar nada, Eloquent no emite `UPDATE` y la
> fila conserva su fecha — no es una prueba de que el guardado falle.

### Errores que salieron al implementar (y cómo quedaron resueltos)

| Error | Causa real | Solución |
|---|---|---|
| `Non-static method Illuminate\Auth\Access\Gate::define()` — **toda la app dejó de arrancar**: `php artisan`, tinker y tests morían en el boot | en `AppServiceProvider` se importó `Illuminate\Auth\Access\Gate` (clase concreta, sin `define()` estático) en vez del facade | `use Illuminate\Support\Facades\Gate;`. El código de §2.5 ya trae el import correcto: **cópialo tal cual** |
| `Class "App\Filament\Settings\SettingsPreviewBuilder" not found` al abrir la página (rompía el preview) | el fichero estaba en `app/Settings/SettingsPreviewBuilder.php` declarando `namespace App\Filament\Settings`. Composer **descarta por PSR-4** un fichero cuya ruta no coincide con el namespace y el classmap optimizado tampoco lo recoge: `class_exists()` devuelve `false` **sin ningún aviso** | moverlo a `app/Filament/Settings/SettingsPreviewBuilder.php` (la ruta que indica §10) y `composer dump-autoload` sin avisos |
| `View [errors.maintenance] not found` en **todas** las peticiones públicas | dos cosas a la vez: faltaba `resources/views/errors/maintenance.blade.php` (§12) **y**, sobre todo, al instalar el middleware se olvidó el guard `if (! setting('maintenance.enabled', false))`. Como la tabla no tenía fila del grupo `maintenance`, `allowed_ips` era `[]` y **todas** las rutas caían en el `return view('errors.maintenance', …)` | crear la vista y dejar ese guard como **primera línea** de `handle()` |
| `ParseError: syntax error, unexpected token ";"` en `AdminPanelProvider.php:61` → **toda la app 500** a las 11:31 UTC del 2026-09-26 y la sensación de que "no se guardan los settings" (ni siquiera se llega a la página) | el bloque `->plugins([ MiaTheme::make() … ])` del final de `panel()` estaba activo con la sintaxis rota: el parser encontró un `;` donde esperaba `]` dentro de ese array, y el error se lanza **durante el boot** (al cargar el provider), antes de que la petición llegue a la ruta. Ruido aguas abajo: `Target class [view] does not exist` y `ReflectionException: Class "view" does not exist` | dejar el bloque **comentado** (tal como está ahora). Diagnóstico en un segundo: `php -l app/Providers/Filament/AdminPanelProvider.php` |
| "Cambié el valor en el panel pero no lo veo en mi cliente MySQL" | (a) el cliente apunta a otro esquema: hay dos en el mismo servidor, `laravel` (5 filas, la de `.env`) y `tramite` (0 filas); (b) no se pulsó *Guardar cambios* — no hay autosave, `->live()` sólo refresca la tarjeta; (c) se guardó sin cambiar nada, y entonces `updated_at` no se mueve | mirar `laravel.settings`, pulsar *Guardar cambios* y comprobar con `SELECT group, updated_at FROM settings` |

### Qué está hecho y qué falta

| Bloque | Estado |
|---|---|
| §2–§11 — migración, modelo, repositorio, provider, `config/settings.php`, helpers, schemas, registry, página, preview, vistas Blade | ✅ hecho |
| §12 — guard del middleware + `errors/maintenance.blade.php` | ✅ hecho y probado (tabla de arriba) |
| `php artisan storage:link` (§2.2) | ✅ hecho → `public/storage` |
| §13 — tests | ⚠️ **parcial**: existe `tests/Feature/SettingsTabsTest.php` (3 tests desde 2026-10-11, **no necesita BD**, pasan ✅). Los cuatro ficheros de §13 siguen **no creados**: `phpunit.xml` fuerza sqlite `:memory:` sin tener `pdo_sqlite` instalado → hay que instalar `php-sqlite3` o apuntar los tests a MySQL antes de escribirlos |
| §15 — theming | ✅ **hecho (2026-09-26)**: existe `resources/css/filament/admin/theme.css` (con `@source` de `app/Filament/**/*` y `resources/views/filament/**/*`), `vite.config.js` lo incluye en `input` y el provider lleva `->viteTheme(...)`. `npm run build` genera `public/build/assets/theme-*.css` con `grid-cols-1`, `lg:grid-cols-3`, `lg:col-span-2` y `lg:sticky` (verificado con `grep` en el CSS compilado), o sea el layout de §11.1 ya sale en dos columnas. **La tarjeta de §11.2 nunca lo necesitó**: es nativa |
| §2.2 — variables `SETTINGS_*` | ⚠️ no están en `.env` ni en `.env.example`; hoy no hacen falta porque los defaults de `config/settings.php` ya coinciden (`upload_disk` = `public`) |
| Fuentes de error de arriba | ✅ los cinco casos ya no se pueden reproducir |
| §11.2 — reescritura de la tarjeta de preview | ✅ hecho (2026-09-25): ahora usa sólo componentes nativos (`x-filament::section`, `avatar`, `badge`, `icon`) y clases `fi-sc-*`, con badge "En vivo". Verificado renderizándola suelta (`view(...)->render()`) en tres estados: completo, sólo logo y vacío |
| §10 — `SettingsPreviewBuilder` endurecido | ✅ hecho (2026-09-25): `resolveFile()` acepta ahora el estado **envuelto en array** (Filament entrega así el `FileUpload` al rehidratar) y toma el primer archivo. Antes devolvía `null` y el logo no se pintaba |
| Guardado end-to-end | ✅ verificado (2026-09-26): página 200 con el botón y `call('save')` real → fila `general` escrita con `updated_at = 2026-09-26 11:53:26` y cache invalidada. Ver bloque citado arriba |
| §16 — `ColorThemeSettingsSchema` (selector del color primario) | ✅ **hecho y verificado (2026-09-27)**: pestaña *Tema del panel* con `Select` + rejilla de 26 swatches (`26 × 11 = 286` tonos renderizados), 26 `$set('data.theme.primary', …)` con la ruta absoluta correcta, la paleta elegida resaltada. Color end-to-end: sin setting → `Amber`, `violet` → `oklch(0.541 0.281 293.009)`, valor inválido → vuelve a `Amber`; `FilamentAsset::renderStyles()` emitió `--primary-600:oklch(0.541 0.281 293.009);`. `call('save')` escribió `{"primary":"violet"}` y redirigió a `manage-settings`. **Sin migración nueva**: reutiliza la tabla `settings`. `vendor/bin/pint --format agent` ✅ · `npm run build` ✅ (las clases nuevas están compiladas en `theme-*.css`) |
| Pruebas dejadas atrás | ✅ la BD se restauró: se borró la fila `theme` creada durante la verificación y quedan sólo los grupos `general, legal, maintenance, seo, social` |
| §7.3 — campo `logo_dark` | ✅ **hecho (2026-09-28)**: segundo `FileUpload` con las mismas opciones que `logo` (`image()`, `imageEditor()`, `disk('public')`, `directory('settings')`, `->live()`). Se guarda en el mismo payload `general`, **sin migración** |
| §10 — `logo_dark` en el preview + URL same-origin | ✅ **hecho (2026-09-28)**: `build()` expone `logo_dark` y `resolveFile()` pasó de `$disk->url()` a `publicUrl()` → `asset('storage/…')`, relativa al origen de la página en curso |
| §11.2 — regla de identidad + alternado claro/oscuro | ✅ **hecho (2026-09-28)**: si hay logo se pinta el logo (y sólo él); si no, el nombre. `logo` lleva `dark:hidden` y `logo_dark` lleva `hidden dark:block`, con las clases ya compiladas en `theme-*.css`. Verificado renderizando el partial en 3 estados: con logo (sin nombre), sin logo (con nombre) y vacío (placeholder) |
| §19 — identidad aplicada al panel | ✅ **hecho y verificado (2026-09-28)**: `brandName`, `brandLogo`, `darkModeBrandLogo`, `favicon` y `homeUrl` en `AdminPanelProvider`. `curl /admin/login` → `<title>… - Caryn Harrison`, los dos `<img class="fi-logo fi-logo-light/dark">` con `src="http://127.0.0.1:8000/storage/…"` (origen de la petición) y `<link rel="icon">`. `Filament::getHomeUrl()` → `https://www.xafesyj.ca` |
| §20 — SEO del sitio público | ✅ **hecho y verificado (2026-09-28)**: `resources/views/partials/seo-head.blade.php` incluido en `welcome.blade.php`. `curl /` → `<title>`, `meta description`, `robots: noindex, nofollow` (la pestaña SEO lo tiene desactivado), `canonical`, `og:*` y favicon |
| **Pestañas agrupadas (General + Redes Sociales)** — *estado histórico* | ✅ hecho (2026-09-28) y **sustituido el 2026-10-11**: Social tenía `tab() = 'general'` y aparecía como bloque dentro de la pestaña *General*. El mecanismo de pestañas compartidas sigue funcionando en `form()`, pero hoy ningún grupo lo usa (ver siguiente fila) |
| **`Redes Sociales` en pestaña propia** | ✅ **hecho (2026-10-11)**: se quitó la sobrescritura de `tab()` en `SocialSettingsSchema`, así que hereda el default de `BaseSettingsSchema` (`tab() = group()` → `'social'`). **La BD no cambia en nada**: sigue siendo el grupo `social`, su propia fila, `setting('social.*')` intacto y candado independiente. Orden de pestañas: `General (10) → Redes Sociales (20) → Términos y Condiciones (30) → Tema del panel (60)` |
| **Pestañas `SEO` y `Mantenimiento` eliminadas del panel** | ✅ **hecho (2026-10-11)**: `SeoSettingsSchema::class` y `MaintenanceSettingsSchema::class` se quitaron del array `schemas` de `config/settings.php` (junto a sus `use`). **Las clases siguen en el proyecto** — una línea en la config restaura cada pestaña — y **los datos de la BD no se tocaron**: `partials/seo-head.blade.php` (§20.1) y `CheckMaintenanceMode` (§12) siguen leyendo `seo.*` y `maintenance.*` con el helper `setting()`. Se perdió la UI para editarlos: hoy el modo mantenimiento sólo se activa escribiendo por BD/tinker (`settings_repository()->put('maintenance', [...])`). Tras el cambio: `php artisan config:clear` + `config:cache`, Pint ✅ y `SettingsTabsTest` ✅ (3 tests: 4 pestañas, `social` con pestaña propia y `seo`/`maintenance` ausentes del registry). **Sin cambios de Blade → no hizo falta `npm run build`** |

## Índice

> **[Estado de la implementación (actualizado 2026-10-11)](#estado-de-la-implementación-actualizado-2026-10-11)**
> — qué está hecho, los errores que salieron al implementar (y su solución)
> y qué sigue pendiente.

1. [Arquitectura y flujo de datos](#1-arquitectura-y-flujo-de-datos)
2. [Instalación](#2-instalación)
3. [Migración de la tabla `settings`](#3-migración-de-la-tabla-settings)
4. [Capa de datos: modelo y repositorio](#4-capa-de-datos-modelo-y-repositorio)
5. [Service Provider](#5-service-provider)
6. [Helper global](#6-helper-global)
7. [Contrato y clases de cada sección (schemas)](#7-contrato-y-clases-de-cada-sección-schemas)
8. [Registry de secciones](#8-registry-de-secciones)
9. [Página de Filament](#9-página-de-filament)
10. [Preview en vivo](#10-preview-en-vivo)
11. [Vistas Blade](#11-vistas-blade)
12. [Uso fuera de Filament: modo mantenimiento](#12-uso-fuera-de-filament-modo-mantenimiento)
13. [Tests](#13-tests)
14. [Cómo agregar una sección nueva](#14-cómo-agregar-una-sección-nueva)
15. [Theming (estilo del panel)](#15-theming-estilo-del-panel)
16. [Color del panel (`ColorThemeSelector`)](#16-color-del-panel-colorthemeselector)
17. [Notas y decisiones de diseño](#17-notas-y-decisiones-de-diseño)
18. [Pendientes y decisiones abiertas](#18-pendientes-y-decisiones-abiertas)
19. [Identidad aplicada al panel (nombre, logo, favicon, home)](#19-identidad-aplicada-al-panel-nombre-logo-favicon-home)
20. [Consumir los settings en plantillas Blade (ejemplos)](#20-consumir-los-settings-en-plantillas-blade-ejemplos)


## 1. Arquitectura y flujo de datos

```
┌─────────────────────────────────────────────────────────────────┐
│                     app/Filament/Pages/ManageSettings.php        │
│  Página Filament: arma los Tabs desde el Registry,               │
│  agrupando por pestaña (§8); guarda y expone el preview.         │
└───────────────┬─────────────────────────────────┬─────────────────┘
                 │                                 │
                 ▼                                 ▼
┌────────────────────────────────┐   ┌─────────────────────────────┐
│ SettingsSchemaRegistry          │   │ SettingsPreviewBuilder       │
│ Resuelve y ordena las clases    │   │ Traduce el estado del form   │
│ registradas en config/settings  │   │ en datos listos para pintar  │
└───────────────┬──────────────────┘   └───────────────┬─────────────┘
                 │                                       │
                 ▼                                       │
┌────────────────────────────────┐                       │
│ HasSettingsSchema (contrato)    │                       │
│ ├─ GeneralSettingsSchema        │◄──────────────────────┘
│ ├─ SocialSettingsSchema         │   (lee el group "general")
│ ├─ LegalSettingsSchema          │
│ ├─ ColorThemeSettingsSchema     │  ← §16 (grupo `theme`)
│ ├─ SeoSettingsSchema            │  ⚠ NO registrado (§7.6)
│ └─ MaintenanceSettingsSchema    │  ⚠ NO registrado (§7.7)
└──────────────────────────────────┘

                 (al guardar / leer)
                          │
                          ▼
┌────────────────────────────────────┐
│ SettingsRepository (contrato)       │
│  └─ EloquentSettingsRepository      │  ← cache + bloqueo + lógica
└───────────────┬──────────────────────┘
                 │
                 ▼
┌────────────────────────────────┐
│ Setting (modelo Eloquent)       │  ← sin lógica, solo casts
└───────────────┬──────────────────┘
                 │
                 ▼
        tabla `settings` (SQLite · portable a MySQL)
   group | name | locked | payload (json)
```

> **Consumidor fuera de la página (§16):** el grupo `theme` es el único
> **registrado** que se lee desde fuera de `ManageSettings`. `AdminPanelProvider`
> pasa a `->colors()` una **closure** que llama a `setting('theme.primary')` y
> devuelve la paleta de Filament correspondiente; Filament la evalúa en
> `Panel::boot()` (middleware `SetUpPanel`, sólo en peticiones del panel) y
> la imprime como `<style>` en el `<head>`. Por eso hay que recargar la
> página para ver el cambio — y por eso el sitio público y los comandos de
> consola **no** tocan la tabla `settings`.
>
> **Y dos grupos no registrados que también se leen fuera (§7.6 / §7.7):**
> `seo.*` (partial `partials/seo-head.blade.php`, §20.1) y `maintenance.*`
> (middleware `CheckMaintenanceMode`, §12). Al estar fuera de
> `config('settings.schemas')` **no aparecen en el panel**, pero el helper
> `setting()` los sigue resolviendo igual desde la tabla.

**Regla de oro:** cada capa solo conoce a la de abajo.
La página no sabe cómo se guarda en BD; el repositorio no sabe qué campos
tiene "General"; el modelo no sabe qué es cache ni bloqueo. Esto permite
cambiar cualquier pieza (ej. cambiar de SQLite a MySQL, o a Redis puro, o
agregar un grupo nuevo) sin tocar las demás.

> **Nota sobre la columna `group`:** `GROUP` es palabra reservada de SQL.
> No es un problema: Laravel escapa todos los identificadores que genera
> (`` ` `` en MySQL, `"` en SQLite), tanto en `Schema` como en el query
> builder de Eloquent. Se usa tal cual, sin renombrar.

**Por qué una fila por grupo:** la tabla usa `unique(group, name)`. En
vez de una fila por cada campo individual, guardamos **todo el payload de
una sección en una sola fila JSON**, con `name = 'config'` fijo. Menos
filas, menos queries, y el `payload` json admite estructuras anidadas sin
migrar la tabla cada vez que agregas un campo.


## 2. Instalación

### 2.0 Estado actual (2026-09-25): todo creado

Filament **ya estaba instalado** (`filament/filament: ^5.8`) y el panel ya
existía (`app/Providers/Filament/AdminPanelProvider.php`, registrado en
`bootstrap/providers.php`), así que no hizo falta `composer require` ni
`filament:install`.

Y **todos** los ficheros de esta guía ya están en el proyecto:

- `database/migrations/2026_09_25_221644_create_settings_table.php` (§3)
- `app/Models/Setting.php` (§4.1)
- `app/Settings/**` (§4.2, §4.3)
- `app/Providers/SettingsServiceProvider.php` + `config/settings.php` (§5)
- `app/helpers.php` (§6)
- `app/Filament/Settings/**` (§7, §8, §10, §16 — incluye `SettingsPreviewBuilder`, **que vive en `app/Filament/Settings/`, no en `app/Settings/`**; PSR-4 o no lo encuentra, y también `ThemeColorPalette.php` y `Schemas/ColorThemeSettingsSchema.php`)
- `app/Filament/Pages/ManageSettings.php` (§9, §16)
- `app/Providers/Filament/AdminPanelProvider.php` (§16 — el `->colors()` dinámico)
- `app/Http/Middleware/CheckMaintenanceMode.php` (§12)
- `resources/views/filament/**` (§11, §16)
- `resources/views/partials/seo-head.blade.php` (§20 — creado 2026-09-28,
  incluido desde `resources/views/welcome.blade.php`)
- `resources/views/errors/maintenance.blade.php` (§12)

Siguen **pendientes** los tests de §13 → ver
[Estado de la implementación](#estado-de-la-implementación-actualizado-2026-10-11).

### 2.1 Copia los archivos

Cada archivo de este documento en la ruta indicada en su encabezado.

### 2.2 Disco de subida público (obligatorio para el preview)

⚠️ **Este es el paso que más se olvida y rompe el preview.**

Por defecto Filament usa `config('filament.default_filesystem_disk') =
env('FILESYSTEM_DISK', 'local')`. En este proyecto `.env` tiene
`FILESYSTEM_DISK=local` → **disco privado** (`storage/app/private/`),
mientras que `SettingsPreviewBuilder` (§10) resuelve las URLs con
`config('settings.upload_disk', 'public')`.

Solución (la que trae la guía): los `FileUpload` ya llevan
`->disk('public')` explícito (§7.3 y §7.6) y el builder lee
`config('settings.upload_disk', 'public')`.

Alternativa global: cambiar `FILESYSTEM_DISK` en `.env` a `public`
(**ojo**, eso cambia el disco por defecto de toda la app, no solo de
Filament — en Filament v5 no existe `FILAMENT_FILESYSTEM_DISK`, el config
es `env('FILESYSTEM_DISK', 'local')`). Si eliges esta vía, **quita** los
`->disk('public')` de los `FileUpload` para que no haya dos fuentes de
verdad.

En cualquier caso **crea el enlace simbólico** para que las URLs públicas
respondan:

```bash
php artisan storage:link
```

Variables nuevas para `.env.example`:

```env
SETTINGS_CACHE_ENABLED=true
SETTINGS_CACHE_STORE=       # vacío = store por defecto (database)
SETTINGS_UPLOAD_DISK=public
```

> **Estado (2026-09-25):** todavía **no** están ni en `.env` ni en
> `.env.example`. No hacen falta: `config/settings.php` ya trae esos
> mismos defaults (`upload_disk` = `public`). Añádelas sólo si vas a
> cambiarlos.

### 2.3 Registra el service provider

En `bootstrap/providers.php` (el archivo ya contiene `AppServiceProvider`
y `AdminPanelProvider`):

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\SettingsServiceProvider::class,
];
```

### 2.4 Registra el helper global en `composer.json`

⚠️ El campo `files` **se añade junto a** `psr-4`, no lo reemplaza:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
        "app/helpers.php"
    ]
}
```

```bash
composer dump-autoload
```

### 2.5 Registra los permisos

En `AppServiceProvider::boot()` — **obligatorio**: `canAccess()` de §9
usa `manage-settings` y la acción de bloqueo usa `manage-settings-lock`;
si no registras las abilities, `$user->can()` devuelve `false` y nadie
puede abrir la página (ver §9.1 / §9.2):

```php
use App\Models\User;
use Illuminate\Support\Facades\Gate;

Gate::define('manage-settings', fn (User $user): bool => true);
Gate::define('manage-settings-lock', fn (User $user): bool => true); // ajústalo
```

### 2.6 Corre la migración

```bash
php artisan migrate
```

### 2.7 Registra el middleware de mantenimiento

En `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\CheckMaintenanceMode::class,
    ]);
})
```

### 2.8 Página auto-descubierta

`AdminPanelProvider` ya declara
`->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')`,
así que `App\Filament\Pages\ManageSettings` **se auto-descubre y no
requiere registro manual**. Si alguna vez quitas esa línea del provider,
la página dejará de aparecer.

### 2.9 Theming

Ver [sección 15](#15-theming-estilo-del-panel).


## 3. Migración de la tabla `settings`

**Estado (2026-09-25): la tabla ya existe y está migrada** (antes sólo
había `users`, `cache` y `jobs`). Una fila por sección (`group`), con el
payload completo en JSON y un candado (`locked`) por fila.

**Archivo (nombre real que generó artisan):**
`database/migrations/2026_09_25_221644_create_settings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // `group` es reservada en SQL, pero Laravel escapa los
            // identificadores, así que es seguro usarla tal cual.
            $table->string('group');
            $table->string('name');

            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();

            // Unicidad de la fila por grupo: name es siempre 'config'.
            $table->unique(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

> ⚠️ **Discrepancia con el fichero real (detectada 2026-09-26).** La
> migración del proyecto escribe `$table->unique('group', 'name')`. En
> `Blueprint::unique($columns, $indexName = null)` el **segundo argumento es
> el nombre del índice**, no las columnas: eso crea un índice único sobre
> `group` **solo** (llamado `name`), no sobre el par `(group, name)` como
> dice este §3. Hoy no molesta porque sólo hay una fila por grupo, pero
> impide un segundo `name` para el mismo `group` y haría fallar un `INSERT`
> con violación de unique si alguna fila usara otro `name`. La forma
> correcta es la de este bloque: `$table->unique(['group', 'name'])`. Para
> corregirlo sobre una tabla ya creada hace falta una migración nueva con
> `$table->dropUnique('name');` y luego `$table->unique(['group', 'name']);`.

### Decisiones respecto a la versión anterior

| Antes | Ahora | Motivo |
|---|---|---|
| `->collation('utf8mb4_bin')` | **quitado** | Existe **sólo en MySQL**; la grammar de SQLite no lo implementa y lo ignora en silencio. La unicidad case-insensitive se garantiza en código (grupos siempre en minúsculas) |
| `->comment(...)` | **quitado** | Mismo motivo: `modifyComment` no existe en SQLite |
| `$table->index('group')` | **quitado** | Redundante: `unique(['group','name'])` ya crea un índice compuesto cuyo prefijo es `group` |
| `$table->softDeletes()` | **quitado** | Ver abajo |

> **Actualizado (2026-09-25):** la BD real de este proyecto es **MariaDB**,
> no SQLite (ver la tabla de entorno). Esos motivos siguen siendo válidos
> *si algún día vuelves a SQLite*, pero aquí `collation`/`comment` no
> romperían nada: se dejan fuera por portabilidad, no por compatibilidad.

**Sobre `softDeletes()`:** nada de esta guía borra filas, y su presencia
es una trampa: `Setting::query()` excluye las filas trashadas, así que
`updateOrCreate()` no las encontraría e intentaría un `INSERT` que
**violcaría el unique `(group, name)`**. Si en el futuro necesitas borrar
settings, hay que usar `withTrashed()` + `restore()` en el repositorio.

Si algún día migras a MySQL y quieres el `collation`/`comment`, añádelos
de vuelta en la migración correspondiente: son compatibles con la tabla
ya creada vía `change()`.


## 4. Capa de datos: modelo y repositorio

### 4.1 Modelo `Setting`

Deliberadamente "tonto": solo define los `casts` (`payload` a array,
`locked` a bool). No sabe nada de cache ni de negocio, así se puede usar
en jobs, comandos o tests sin arrastrar dependencias.

**Archivo:** `app/Models/Setting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $group
 * @property string $name
 * @property bool $locked
 * @property array<string, mixed> $payload
 */
class Setting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'name',
        'locked',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'locked' => 'boolean',
        ];
    }
}
```

> Se usa el **método `casts()`** (Laravel 11+) y no la propiedad
> `protected $casts`: es la forma actual y la que usa la documentación de
> Filament. **No se usa `SoftDeletes`** — ver la nota de §3.

### 4.2 Contrato `SettingsRepository`

Define el vocabulario con el que el resto de la app habla con la
configuración: `get`, `all`, `put`, `merge`, `lock`, `unlock`, `isLocked`,
`forget`, `flush`. Al ser una interfaz, mañana podrías escribir una
implementación distinta (ej. 100% Redis, o una API externa) sin cambiar ni
una línea de la página de Filament ni del helper `setting()`.

**Archivo:** `app/Settings/Contracts/SettingsRepository.php`

```php
<?php

namespace App\Settings\Contracts;

interface SettingsRepository
{
    /**
     * Obtiene un valor. Sin $key devuelve el payload completo del grupo.
     * Con $key usa dot-notation sobre el payload (ej: "seo" -> "meta_title").
     */
    public function get(string $group, ?string $key = null, mixed $default = null): mixed;

    /**
     * Devuelve el payload completo (array) de un grupo.
     */
    public function all(string $group): array;

    /**
     * Reemplaza el payload completo de un grupo. Lanza RuntimeException
     * si el grupo está bloqueado.
     */
    public function put(string $group, array $payload): void;

    /**
     * Actualiza solo algunas claves del payload de un grupo (merge),
     * sin pisar el resto de los valores ya guardados.
     */
    public function merge(string $group, array $partialPayload): void;

    public function lock(string $group): void;

    public function unlock(string $group): void;

    public function isLocked(string $group): bool;

    /**
     * Invalida la cache de un grupo puntual.
     */
    public function forget(string $group): void;

    /**
     * Invalida la cache de todos los grupos que existan en la tabla.
     */
    public function flush(): void;
}
```

### 4.3 Implementación `EloquentSettingsRepository`

Aquí vive toda la lógica real:

- **Lectura con cache** (`rememberForever`), una key de cache por grupo
  (`settings:{group}`).
- **Escritura** (`put`) que primero verifica que el grupo no esté
  bloqueado (lanza `RuntimeException` si lo está) y luego hace
  `updateOrCreate` + invalida la cache de ese grupo.
- **`merge()`** para actualizar solo algunas claves del payload sin pisar
  el resto (útil si en el futuro guardas desde dos formularios distintos
  al mismo grupo).
- **`lock()` / `unlock()` / `isLocked()`** para el sistema de bloqueo.
- **`get()`** soporta dot-notation sobre el payload gracias a
  `Arr::get()` (ej. `get('social', 'facebook')`).
- **`flush()`** recorre los grupos que existen en la tabla, sin leer
  `config('settings.schemas')` (así esta capa no se acopla a la capa
  Filament).

La configuración llega por **constructor (property promotion)**, inyectada
desde el service provider (§5): el repositorio ya no llama a `config()`
por su cuenta, lo que lo hace fácil de construir en tests.

**Archivo:** `app/Settings/EloquentSettingsRepository.php`

```php
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
```


## 5. Service Provider

Ata la interfaz `SettingsRepository` a `EloquentSettingsRepository` como
singleton (una sola instancia por request, cache compartida) y registra el
`SettingsSchemaRegistry` también como singleton. La configuración se lee
**aquí** y se inyecta por constructor, así que el repositorio no llama a
`config()`.

También publica el `config/settings.php` por si quieres editarlo desde
`config:publish`.

**Archivo:** `app/Providers/SettingsServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Filament\Settings\SettingsSchemaRegistry;
use App\Settings\Contracts\SettingsRepository;
use App\Settings\EloquentSettingsRepository;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/settings.php', 'settings');

        $this->app->singleton(SettingsRepository::class, function (): EloquentSettingsRepository {
            return new EloquentSettingsRepository(
                settingName: (string) config('settings.setting_name', 'config'),
                cacheEnabled: (bool) config('settings.cache.enabled', true),
                cacheStore: config('settings.cache.store'),
                cachePrefix: (string) config('settings.cache.prefix', 'settings'),
            );
        });

        $this->app->singleton(SettingsSchemaRegistry::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');
    }
}
```

> **Nota:** `mergeConfigFrom()` hace `require` del archivo, así que
> `config/settings.php` **tiene que existir** en el repositorio (abajo) en
> el momento del boot; no sirve con publicarlo después.

**Archivo de configuración asociado:** `config/settings.php`

```php
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
```

> **Actual (2026-10-11): el array `schemas` trae sólo 4 entradas.**
> `SeoSettingsSchema::class` y `MaintenanceSettingsSchema::class` se quitaron
> a propósito (§7.6 / §7.7): sus clases siguen en el proyecto pero no están
> registradas, así que no generan pestaña en el panel. Caché de config
> aplicada con `php artisan config:clear` + `php artisan config:cache`
> (`bootstrap/cache/config.php` debe quedar **sin** esas dos clases; si no,
> la app sigue mostrando las pestañas viejas).


## 6. Helper global

Dos funciones globales para no tener que inyectar el repositorio en todos
lados. `setting()` parte la clave por el primer punto: todo lo que va
antes es el `group`, el resto es la ruta dot-notation dentro del payload.

**Archivo:** `app/helpers.php`

```php
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
```

**Ejemplos de uso en cualquier parte de la app:**

```php
setting('social.facebook');                 // valor puntual
setting('social');                          // array completo del grupo
setting('maintenance.enabled', false);      // con valor por defecto
settings_repository()->put('seo', [...]);   // escritura directa
settings_repository()->lock('legal');       // bloquear una sección
```

En una vista Blade pública (footer, por ejemplo):

```blade
<a href="{{ setting('social.facebook') }}">Facebook</a>
```


## 7. Contrato y clases de cada sección (schemas)

### 7.1 Contrato `HasSettingsSchema`

Cada sección del panel es una clase que implementa esta interfaz. Define:
identificador de grupo, **pestaña en la que se muestra**, etiqueta, icono,
orden, columnas del formulario y el array de componentes Filament.

**Archivo:** `app/Filament/Settings/Contracts/HasSettingsSchema.php`

```php
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
```

> **`tab()` = pestaña, `group()` = fila de la BD.** Son cosas distintas a
> propósito: `group()` es la clave de almacenamiento (una fila por grupo en
> `settings`) y `tab()` es sólo dónde se pinta. **Hoy ningún esquema
> sobrescribe `tab()`**: los cuatro registrados (`general`, `social`,
> `legal`, `theme`) tienen pestaña propia (§7.4, §7.6). El mecanismo de
> agrupación se mantiene — basta con devolver el `group()` de otro esquema,
> p. ej. `return 'general';` — y `ManageSettings::form()` sigue contemplando
> ese caso con una `Section` por grupo dentro de la pestaña compartida
> (§9). Ver §14 ("meter la sección en una pestaña existente").

### 7.2 Clase base `BaseSettingsSchema`

Evita repetir `sort()`, `columns()` y `tab()` en cada clase concreta: la
implementación por defecto de `tab()` es **"pestaña propia"**, así que una
sección nueva no se entera de nada de esto salvo que quiera unirse a
otra pestaña.

**Archivo:** `app/Filament/Settings/Schemas/BaseSettingsSchema.php`

```php
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
```

### 7.3 `GeneralSettingsSchema`

Nombre, URL, logo (claro y modo oscuro), favicon, descripción, contacto.
Los campos que alimentan el preview usan `->live(onBlur: true)` (texto) o
`->live()` (archivos) para refrescar la tarjeta de la derecha sin guardar.

> **Los cuatro primeros campos son los que personalizan el panel** (§19):
> `site_name` → nombre de marca, `logo` → logo claro, `logo_dark` → logo en
> modo oscuro, `favicon` → `<link rel="icon">`, y `site_url` → destino del
> enlace del logo. `logo_dark` y la regla de visibilidad son del 2026-09-28.

**Archivo:** `app/Filament/Settings/Schemas/GeneralSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class GeneralSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 10;

    protected int $columns = 2;

    public function group(): string
    {
        return 'general';
    }

    public function label(): string
    {
        return 'General';
    }

    public function icon(): string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public function schema(): array
    {
        return [
            TextInput::make('site_name')
                ->label('Nombre del sitio')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true),

            TextInput::make('site_url')
                ->label('URL del sitio')
                ->url()
                ->maxLength(255),

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->imageEditor()
                ->disk('public') // ver nota §7.3.1
                ->directory('settings')
                ->live(),

            FileUpload::make('logo_dark')
                ->label('Logo (modo oscuro)')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('settings')
                ->live(),

            FileUpload::make('favicon')
                ->label('Favicon')
                ->image()
                ->disk('public')
                ->directory('settings')
                ->live(),

            Textarea::make('description')
                ->label('Descripción del sitio')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull()
                ->live(onBlur: true),

            TextInput::make('support_email')
                ->label('Correo de soporte')
                ->email()
                ->maxLength(255)
                ->live(onBlur: true),

            TextInput::make('support_phone')
                ->label('Teléfono de soporte')
                ->tel()
                ->live(onBlur: true),
        ];
    }
}
```

#### 7.3.1 Nota: por qué `->disk('public')` es obligatorio

Sin `->disk(...)`, `FileUpload` usa
`config('filament.default_filesystem_disk')` = `env('FILESYSTEM_DISK', 'local')`
→ **disco privado** (`storage/app/private/`). En este proyecto `.env` tiene
`FILESYSTEM_DISK=local`.

Mientras tanto `SettingsPreviewBuilder` (§10) resuelve las URLs leyendo
`config('settings.upload_disk', 'public')`, o sea **otro disco**. Con el
archivo subido en `private` y el builder mirando `public`:

- `exists()` devuelve `false` → `url` y `size` salen `null`,
- el preview nunca muestra el logo,
- y el sitio público no puede cargarlo (no hay enlace simbólico ni permisos).

Por eso los cuatro `FileUpload` llevan `->disk('public')` explícito. Debe
coincidir con `config('settings.upload_disk')` (§5), y necesitas haber
hecho `php artisan storage:link` (§2.2).

> `logo_dark` usa exactamente la misma configuración que `logo` (§7.3) y
> vive en la misma carpeta `settings/`: el logo claro y el oscuro son dos
> archivos distintos del mismo grupo `general`, y §19 es el que decide
> cuál se pinta en el panel según el modo del tema.

### 7.4 `SocialSettingsSchema`

Tiene **pestaña propia** desde el 2026-10-11: antes sobrescribía `tab()`
para pintarse dentro de *General*, y ahora hereda el default de
`BaseSettingsSchema` (`tab() = group()` → `'social'`), así que sus 7
campos salen en la pestaña *Redes Sociales* (segunda de la barra, entre
*General* y *Términos y Condiciones*). El grupo sigue siendo `social`:
una fila aparte en la BD, `setting('social.*')` sin cambios y candado
independiente — **el cambio fue puramente visual**.

**Archivo:** `app/Filament/Settings/Schemas/SocialSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\TextInput;

class SocialSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 20;

    protected int $columns = 2;

    public function group(): string
    {
        return 'social';
    }

    public function label(): string
    {
        return 'Redes Sociales';
    }

    public function icon(): string
    {
        return 'heroicon-o-share';
    }

    public function schema(): array
    {
        return [
            TextInput::make('facebook')
                ->label('Facebook')
                ->prefixIcon('heroicon-o-globe-alt')
                ->url()
                ->maxLength(255),

            TextInput::make('instagram')
                ->label('Instagram')
                ->url()
                ->maxLength(255),

            TextInput::make('x')
                ->label('X (Twitter)')
                ->url()
                ->maxLength(255),

            TextInput::make('tiktok')
                ->label('TikTok')
                ->url()
                ->maxLength(255),

            TextInput::make('youtube')
                ->label('YouTube')
                ->url()
                ->maxLength(255),

            TextInput::make('linkedin')
                ->label('LinkedIn')
                ->url()
                ->maxLength(255),

            TextInput::make('whatsapp')
                ->label('WhatsApp')
                ->tel()
                ->helperText('Incluye código de país, ej: +51999999999'),
        ];
    }
}
```

**Notas:**

- **Sin `tab()`**: el código de arriba es exactamente el fichero actual. El
  único cambio respecto a la versión anterior (2026-10-11) fue **borrar** la
  sobrescritura de `tab()`; el default de `BaseSettingsSchema` (§7.2) devuelve
  `group()`, o sea `'social'`. No hay que añadir nada para darle pestaña
  propia.
- **`sort()` vuelve a ordenar pestañas**: con `20`, *Redes Sociales* se
  sitúa justo detrás de *General* (10) y delante de *Términos y
  Condiciones* (30) y *Tema del panel* (60).
- `label()` e `icon()` son los de la pestaña: "Redes Sociales" con
  `heroicon-o-share`.
- Resultado en el panel: **4 pestañas** — `General`, `Redes Sociales`,
  `Términos y Condiciones` y `Tema del panel`. Cada una con su propio
  `statePath`, guardado, cache y bloqueo.
- Si en el futuro quieres **volver a agruparla dentro de *General***, basta
  con reañadir (§7.1 / §14):
  ```php
  public function tab(): string
  {
      return 'general';
  }
  ```
  Nada más: la BD, los `casts` y los candados no cambian, y
  `ManageSettings::form()` ya sabe pintarla como `Section` dentro de la
  pestaña compartida.

### 7.5 `LegalSettingsSchema`

**Archivo:** `app/Filament/Settings/Schemas/LegalSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;

class LegalSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 30;

    protected int $columns = 1;

    public function group(): string
    {
        return 'legal';
    }

    public function label(): string
    {
        return 'Términos y Condiciones';
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function schema(): array
    {
        return [
            Toggle::make('require_acceptance')
                ->label('Exigir aceptación explícita al registrarse')
                ->default(true),

            DatePicker::make('last_updated_at')
                ->label('Última actualización')
                ->native(false),

            RichEditor::make('terms')
                ->label('Términos y condiciones')
                ->columnSpanFull(),

            RichEditor::make('privacy_policy')
                ->label('Política de privacidad')
                ->columnSpanFull(),

            RichEditor::make('cookies_policy')
                ->label('Política de cookies')
                ->columnSpanFull(),
        ];
    }
}
```

### 7.6 `SeoSettingsSchema`

> ⚠️ **NO registrado desde el 2026-10-11.** Su clase existe en el proyecto
> (el código de abajo es el fichero real) pero `SeoSettingsSchema::class`
> **ya no está en el array `schemas` de `config/settings.php`** (§5), así
> que el panel **no muestra la pestaña SEO** y sus campos no se pueden editar
> desde la UI. Consecuencias:
>
> - Los valores ya guardados en la BD (`seo.*`) **siguen funcionando**: el
>   partial `resources/views/partials/seo-head.blade.php` (§20.1) los lee con
>   `setting()` y los imprime en el `<head>` de la web pública tal cual.
> - `registry()->find('seo')` devuelve `null` y el grupo no aparece ni en las
>   pestañas ni en el selector de bloqueo.
> - Para volver a habilitarla basta con añadir una línea al array de §5
>   (más sus dos `use`) y limpiar la caché de config. No hay que tocar la
>   página ni el repositorio.
> - Para cambiar un valor **sin UI**: `settings_repository()->put('seo', [...])`
>   desde tinker, o un `UPDATE` directo sobre `settings` (grupo `seo`).

**Archivo:** `app/Filament/Settings/Schemas/SeoSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class SeoSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 40;

    protected int $columns = 2;

    public function group(): string
    {
        return 'seo';
    }

    public function label(): string
    {
        return 'SEO';
    }

    public function icon(): string
    {
        return 'heroicon-o-magnifying-glass';
    }

    public function schema(): array
    {
        return [
            TextInput::make('meta_title')
                ->label('Meta título')
                ->maxLength(60)
                ->columnSpanFull(),

            Textarea::make('meta_description')
                ->label('Meta descripción')
                ->rows(3)
                ->maxLength(160)
                ->columnSpanFull(),

            TextInput::make('meta_keywords')
                ->label('Palabras clave')
                ->helperText('Separadas por coma')
                ->columnSpanFull(),

            FileUpload::make('og_image')
                ->label('Imagen Open Graph')
                ->image()
                ->disk('public')
                ->directory('settings')
                ->columnSpanFull(),

            TextInput::make('canonical_url')
                ->label('URL canónica')
                ->url(),

            Toggle::make('indexable')
                ->label('Permitir indexación (robots)')
                ->default(true),

            TextInput::make('google_analytics_id')
                ->label('Google Analytics ID'),

            TextInput::make('google_search_console')
                ->label('Google Search Console (verificación)'),
        ];
    }
}
```

### 7.7 `MaintenanceSettingsSchema`

Incluye `allowed_ips` (`TagsInput`), consumido luego por el middleware de
mantenimiento (sección 12).

> ⚠️ **NO registrado desde el 2026-10-11.** Igual que §7.6: la clase existe
> (el código de abajo es el fichero real) pero `MaintenanceSettingsSchema::class`
> **salió del array `schemas` de `config/settings.php`** (§5), así que el
> panel ya **no muestra la pestaña Mantenimiento**. Consecuencias:
>
> - `CheckMaintenanceMode` (§12) **sigue activo** y sigue leyendo
>   `maintenance.enabled`, `maintenance.message` y `maintenance.allowed_ips`
>   con `setting()`: los valores guardados no se tocaron.
> - Pero **ya no hay UI para activar el modo mantenimiento** ni para editar
>   el mensaje o las IPs. Si la BD tiene `maintenance.enabled = true`, la web
>   pública se queda en 503 hasta que lo cambies a mano:
>   `settings_repository()->merge('maintenance', ['enabled' => false])`
>   desde tinker (con la app viva, la cache se invalida sola).
> - Para devolver la pestaña: una línea en el array de §5 (más su `use`) y
>   `php artisan config:clear`.

**Archivo:** `app/Filament/Settings/Schemas/MaintenanceSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class MaintenanceSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 50;

    protected int $columns = 1;

    public function group(): string
    {
        return 'maintenance';
    }

    public function label(): string
    {
        return 'Mantenimiento';
    }

    public function icon(): string
    {
        return 'heroicon-o-wrench-screwdriver';
    }

    public function schema(): array
    {
        return [
            Toggle::make('enabled')
                ->label('Modo mantenimiento activo')
                ->live()
                ->helperText('Al activarlo, los visitantes verán el mensaje de abajo en lugar del sitio.'),

            Textarea::make('message')
                ->label('Mensaje a mostrar a los visitantes')
                ->rows(2)
                ->default('Estamos en mantenimiento. Vuelve pronto.'),

            TagsInput::make('allowed_ips')
                ->label('IPs permitidas durante el mantenimiento')
                ->helperText('Estas IPs seguirán viendo el sitio normalmente.')
                ->placeholder('Escribe una IP y presiona Enter'),
        ];
    }
}
```


### 7.8 Puntero: el sexto schema (`ColorThemeSettingsSchema`, §16)

El selector del color primario **no se documenta aquí** porque no es una
sección aislada: viene con su catálogo de paletas (`ThemeColorPalette`), su
rejilla de swatches en Blade y su consumidor en `AdminPanelProvider`. Todo el
código del módulo está en **§16 [Color del panel](#16-color-del-panel-colorthemeselector)**.


## 8. Registry de secciones

Lee `config('settings.schemas')`, instancia cada clase vía el contenedor
(`app($class)`) y las ordena por `sort()`. La página de Filament nunca
itera sobre clases hardcodeadas: siempre pasa por este registry.

Además expone **`byTab()`**, que agrupa los esquemas por la pestaña en la
que se pintan (`tab()`) manteniendo el orden de `sort()`: es lo que usa
§9 para decidir cuántas pestañas armar. La clave de cada grupo es el
`group()` del esquema **dueño** de esa pestaña (el que declara
`group() === tab()`).

**Archivo:** `app/Filament/Settings/SettingsSchemaRegistry.php`

```php
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
```


## 9. Página de Filament

Orquesta todo: en `form()` recorre el registry **agrupado por pestaña**
(`byTab()`, §8) y arma un `Tab` por cada grupo; si ese grupo tiene un solo
schema se comporta como siempre (`Tab` con `statePath(group)`), y si tiene
varios — **hoy no hay ningún caso** (los 4 grupos registrados tienen
pestaña propia desde 2026-10-11), pero el camino queda preparado — la
pestaña va **sin `statePath`** y cada
schema se pinta en su propia `Section`, que sí conserva
`statePath(group)` — así se ve todo junto, pero la BD, el guardado y el
candado por sección siguen siendo los mismos. Cada bloque sale
deshabilitado si `isLocked()` es `true`. En `mount()` carga los
valores guardados de cada grupo; en `save()` persiste cada grupo (saltando
los bloqueados y avisando por notificación); expone una acción de
cabecera para bloquear/desbloquear secciones; y expone
`getGeneralPreview()` para la tarjeta de preview.

El código de abajo es **el fichero actual, tal cual**
(`app/Filament/Pages/ManageSettings.php`): incluye el
`$navigationGroup = 'System'` de la navegación del panel y el bloque
**TEMPORAL** de diagnóstico dentro de `save()` (loguea con `Log::info` qué
llega del navegador y qué había antes/después en la BD, más el listado de
`skipped`), montado para cerrar el incidente de "no se guarda en la BD".
Está documentado aquí para que no te lo encuentres de sorpresa: **borra
ese bloque (las variables `$bdAntes` y el `Log::info`) en cuanto se
confirme la causa** — escribe en el log de cada guardado un volcado
completo de la tabla.

También cierra la puerta con `canAccess()` (§9.1) y decide quién ve la
acción de bloqueo con `visible()` (§9.2) — las dos usan abilities que hay
que registrar en §2.5.

**Archivo:** `app/Filament/Pages/ManageSettings.php`

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Settings\Contracts\HasSettingsSchema;
use App\Filament\Settings\SettingsPreviewBuilder;
use App\Filament\Settings\SettingsSchemaRegistry;
use App\Models\Setting;
use App\Settings\Contracts\SettingsRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use UnitEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del panel';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.manage-settings';
    protected static string|UnitEnum|null $navigationGroup = 'System';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = $this->registry()->all()
            ->mapWithKeys(fn(HasSettingsSchema $schema) => [
                $schema->group() => $this->repository()->all($schema->group()),
            ])
            ->all();

        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        $repository = $this->repository();

        $tabs = $this->registry()->byTab()
            ->map(function (Collection $settingsSchemas) use ($repository) {
                $isLocked = fn(HasSettingsSchema $settingsSchema): bool => $repository->isLocked($settingsSchema->group());

                // Esquema dueño de la pestaña: el que declara esa pestaña como
                // suya aporta etiqueta e icono.
                $owner = $settingsSchemas->first(
                    fn(HasSettingsSchema $settingsSchema) => $settingsSchema->group() === $settingsSchema->tab()
                ) ?? $settingsSchemas->first();

                // Pestaña de una sola sección: la pestaña es la dueña del
                // `statePath` del grupo, igual que siempre.
                if ($settingsSchemas->count() === 1) {
                    return Tab::make($owner->label())
                        ->icon($owner->icon())
                        ->statePath($owner->group())
                        ->columns($owner->columns())
                        ->disabled($isLocked($owner))
                        ->schema($owner->schema());
                }

                // Varios grupos comparten pestaña. La pestaña NO puede llevar
                // `statePath`: los hijos se resuelven contra el contenedor, así
                // que dentro de `statePath('general')` un hijo `social`
                // acabaría en `data.general.social`. Sin `statePath` en la
                // pestaña, cada esquema va en su propia Section, que sí
                // conserva `statePath(group)`: `save()` sigue recibiendo
                // `general` y `social` por separado, la fila de la BD no cambia
                // y cada bloque se bloquea (y se omite al guardar) por su cuenta.
                return Tab::make($owner->label())
                    ->icon($owner->icon())
                    ->schema(
                        $settingsSchemas->map(
                            fn(HasSettingsSchema $settingsSchema) => Section::make($settingsSchema->label())
                                ->icon($settingsSchema->icon())
                                ->statePath($settingsSchema->group())
                                ->columnSpanFull()
                                ->columns($settingsSchema->columns())
                                ->disabled($isLocked($settingsSchema))
                                ->schema($settingsSchema->schema())
                        )->all()
                    );
            })
            ->values()
            ->all();

        return $schema
            ->components([
                Tabs::make('settings')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs($tabs),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $repository = $this->repository();

        // Filament **excluye del estado** cualquier componente deshabilitado
        // (`Component::disabled()` → `isDehydrated() === false`), y las Tabs
        // deshidratan sus hijos (hoy cada bloque vive en su `Section`, con su
        // propio `statePath`). Así que las secciones bloqueadas al cargar
        // la página NO llegan al foreach de abajo: las detectamos por
        // diferencia entre lo que montamos y lo que Filament devuelve.
        $state = $this->form->getState();
        $skipped = array_values(array_diff(array_keys($this->data), array_keys($state)));

        // TEMPORAL (diagnóstico de "no se guarda en la BD"): guarda qué llega del
        // navegador y qué había antes de escribir. Se elimina al confirmar la causa.
        $bdAntes = Setting::query()->get(['group', 'payload'])
            ->mapWithKeys(fn(Setting $setting) => [$setting->group => $setting->payload])
            ->all();

        foreach ($state as $group => $payload) {
            // Carrera: la sección pudo bloquearse con el formulario abierto,
            // en cuyo caso sí está en el estado pero hay que ignorarla.
            if ($repository->isLocked($group)) {
                $skipped[] = $group;

                continue;
            }

            $repository->put($group, $payload ?? []);
        }

        $skipped = array_values(array_unique($skipped));

        Log::info('ManageSettings::save', [
            'state' => $state,
            'propiedad' => $this->data,
            'bd_antes' => $bdAntes,
            'bd_despues' => Setting::query()->get(['group', 'payload'])
                ->mapWithKeys(fn(Setting $setting) => [$setting->group => $setting->payload])
                ->all(),
            'skipped' => $skipped,
        ]);

        if ($skipped !== []) {
            Notification::make()
                ->title('Algunas secciones están bloqueadas')
                ->body('No se guardaron cambios en: ' . implode(', ', $skipped))
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('Configuración guardada correctamente')
                ->success()
                ->send();
        }

        // Refresca el formulario con lo que realmente quedó persistido.
        $this->mount();

        // Los colores del panel se imprimen en el <head> durante el render de la
        // página, así que Livewire no puede pintarlos en caliente. Se recarga la
        // misma URL para que un cambio de color se vea en el acto. Las
        // notificaciones viven en la sesión, por lo que sobreviven a la recarga.
        $this->redirect(url()->previous(route('filament.admin.pages.manage-settings')));
    }

    /**
     * Autorización de la página. `canAccess()` de Filament devuelve `true`
     * por defecto, así que sin esto cualquier usuario autenticado entra a
     * /admin/configuracion.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-settings') ?? false;
    }

    /**
     * Acción de cabecera opcional para bloquear/desbloquear una sección
     * completa desde la UI (por ejemplo, para que solo un super-admin
     * pueda tocar "Legal" una vez publicado). Ajusta el `visible()`
     * a tu política de permisos real (Gate/Policy).
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleLock')
                ->label('Bloquear / desbloquear sección')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->schema([
                    Select::make('group')
                        ->label('Sección')
                        ->options(
                            $this->registry()->all()
                                ->mapWithKeys(fn(HasSettingsSchema $s) => [$s->group() => $s->label()])
                        )
                        ->required(),

                    Toggle::make('locked')
                        ->label('Bloqueada')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $data['locked']
                        ? $this->repository()->lock($data['group'])
                        : $this->repository()->unlock($data['group']);

                    Notification::make()
                        ->title('Estado de bloqueo actualizado')
                        ->success()
                        ->send();

                    $this->mount();
                })
                ->visible(fn(): bool => auth()->user()?->can('manage-settings-lock') ?? false),
        ];
    }

    /**
     * Datos listos para pintar en el panel de preview (columna derecha).
     * Se recalcula en cada render, así que refleja el estado actual del
     * formulario aunque todavía no se haya guardado.
     */
    public function getGeneralPreview(): array
    {
        return app(SettingsPreviewBuilder::class)->build($this->data['general'] ?? []);
    }

    protected function registry(): SettingsSchemaRegistry
    {
        return app(SettingsSchemaRegistry::class);
    }

    protected function repository(): SettingsRepository
    {
        return app(SettingsRepository::class);
    }
}
```

#### 9.1 Nota: autorización de la página (`canAccess()`)

Filament no restringe las páginas por defecto:
`Filament\Pages\Concerns\CanAuthorizeAccess::canAccess()` devuelve `true`
y su comentario lo dice literalmente: *"Custom pages default to allowing
access for all authenticated panel users"*. Sin declararlo, **cualquier
usuario autenticado** llega a `/admin/configuracion`.

El trait lo usa así (dos veces):

```php
public function mountCanAuthorizeAccess(): void
{
    abort_unless(static::canAccess(), 403);
}

public function hydrateCanAuthorizeAccess(): void
{
    abort_unless(static::canAccess(), 403);
}
```

O sea: se comprueba al abrir la página **y en cada request de Livewire**
(si pierdes el permiso en caliente, todo lo que hagas en el form responde
403).

El `?? false` delimita a los invitados. Ojo con las abilities: si **no**
las registras con `Gate::define(...)` (§2.5), `$user->can()` devuelve
`false` y la página queda **inaccesible para todo el mundo** (incluido el
admin). Ese es el motivo de §2.5.

#### 9.2 Nota: la acción de bloqueo (`visible()`)

El `?? true` original fallaba en ambos sentidos:

- **logueado sin la ability**: `auth()->user()?->can(...)` → `false` → la
  acción nunca se veía;
- **invitado**: `auth()->user()` → `null` → `?? true` → la acción **se
  mostraba** (aunque luego el login la bloqueara, el menú aparecía).

Con `?? false` el comportamiento es coherente: sin sesión no se ve nada, y
con sesión se ve sólo si el Gate lo permite.

> **Recordatorio de diseño:** `getHeaderActions()` es `protected`, no
> `public`, y en Filament v5 `Action::form(...)` está deprecado: se usa
> `Action::schema([...])` (ya reflejado en el código).

#### 9.3 Nota: por qué `save()` compara por diferencia

La fuente, en `Filament\Schemas\Components\Concerns\HasState`:

```php
public function isDehydrated(): bool
{
    $isDehydrated = $this->evaluate($this->isDehydrated) ?? $this->isSaved();
    ...
}

public function dehydrateState(array &$state, bool $isDehydrated = true): void
{
    if (! ($isDehydrated && $this->isDehydrated())) {
        ...
        Arr::forget($state, $statePath);
    }
}
```

y `Filament\Schemas\Components\Concerns\CanBeDisabled::disabled()`:

```php
public function disabled(bool | Closure $condition = true): static
{
    // Security: Disabling a field prevents it from being saved, but
    // skilled users can manipulate Livewire's JavaScript to bypass
    // the disabled state on the client. Always enforce authorization
    // on the backend ... for sensitive fields.

    $this->isDisabled = $condition;
    $this->saved(fn (Component $component): bool => ! $component->evaluate($condition));

    return $this;
}
```

O sea: un componente deshabilitado **no se salva, por tanto no se
deshidrata, por tanto su `statePath` se borra del estado**. Como el
contenedor de cada bloque tiene `statePath` igual a su `group` (el `Tab`
cuando la pestaña es de una sola sección, y la `Section` cuando varios
grupos comparten pestaña, §9), las secciones bloqueadas desaparecen de
`getState()` y `getState()` ni siquiera las valida. El
comentario de seguridad de Filament es además el motivo de mantener la
doble barrera: el `isLocked()` dentro del `foreach` de `save()`, que es
lo que de verdad impide escribir.

Consecuencias:

- comparar `array_keys($this->data)` con `array_keys($state)` es la única
  forma fiable de saber qué secciones no se guardaron;
- **no se validan** las secciones bloqueadas (es el comportamiento
  deseado);
- cuidado si algún grupo llegara a tener *todos* sus campos no
  deshidratados (`->dehydrated(false)` / `->hidden()` sin
  `dehydratedWhenHidden()`): aparecería como "bloqueado" en el aviso sin
  estarlo. Ninguna de las secciones de esta guía hace eso.

## 10. Preview en vivo

`SettingsPreviewBuilder` traduce el estado *crudo* del formulario (que
puede tener un archivo recién elegido en el navegador, todavía sin
guardar) en datos ya listos para la vista:

- Si el estado llega **envuelto en array** (así lo entrega Filament al
  rehidratar el `FileUpload`), toma el primer archivo antes de nada.
- Si `logo`/`logo_dark`/`favicon` es un `TemporaryUploadedFile` (archivo
  recién subido, aún no persistido), usa su URL temporal de Livewire.
- Si ya es un string (ruta guardada en el disco configurado en
  `settings.upload_disk`, por defecto `public`), resuelve la URL pública
  con `asset()` — **relativa al origen de la página en curso**, ver §19.4 —
  y el tamaño real del archivo.
- Si está vacío, devuelve `null` (la vista simplemente no muestra esa
  fila).

También expone `logo_dark` (§7.3), que es el que permite que el preview
muestre el logo correcto según el modo claro/oscuro (§11.2).

Vive separado de la página a propósito: se puede testear solo, y si mañana
agregas un preview para "SEO" (cómo se ve en Google) o "Legal", repites
el mismo patrón sin tocar `ManageSettings`.

**Archivo:** `app/Filament/Settings/SettingsPreviewBuilder.php`

```php
<?php

namespace App\Filament\Settings;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Traduce el estado "crudo" del formulario (que puede contener archivos
 * recién subidos, todavía no guardados) en datos listos para pintar en el
 * panel de preview: URLs, nombre de archivo y tamaño legible.
 *
 * Vive fuera de la página de Filament a propósito: así se puede testear
 * de forma aislada y reutilizar si en el futuro agregas más previews
 * (ej. una preview de "Términos" o de "SEO / resultado en Google").
 */
class SettingsPreviewBuilder
{
    public function build(array $general): array
    {
        return [
            'site_name' => $general['site_name'] ?? null,
            'description' => $general['description'] ?? null,
            'support_email' => $general['support_email'] ?? null,
            'support_phone' => $general['support_phone'] ?? null,
            'logo' => $this->resolveFile($general['logo'] ?? null, 'Logo'),
            'logo_dark' => $this->resolveFile($general['logo_dark'] ?? null, 'Logo (modo oscuro)'),
            'favicon' => $this->resolveFile($general['favicon'] ?? null, 'Favicon'),
        ];
    }

    /**
     * @return array{label: string, name: string, url: ?string, size: ?string}|null
     */
    protected function resolveFile(mixed $value, string $label): ?array
    {
        if (blank($value)) {
            return null;
        }

        // Filament entrega el estado como array (incluso con un solo archivo
        // cuando el campo se rehidrata desde la caché); aquí sólo interesa
        // el primer archivo, igual que en el formulario.
        if (is_array($value)) {
            $value = Arr::first($value);

            if (blank($value)) {
                return null;
            }
        }

        // Archivo recién seleccionado en el navegador, aún no guardado.
        if ($value instanceof TemporaryUploadedFile) {
            return [
                'label' => $label,
                'name' => $value->getClientOriginalName(),
                'url' => $value->temporaryUrl(),
                'size' => $this->humanFileSize($value->getSize()),
            ];
        }

        // Ruta ya persistida en el disco configurado.
        if (is_string($value)) {
            $disk = $this->disk();
            $exists = $disk->exists($value);

            return [
                'label' => $label,
                'name' => basename($value),
                'url' => $exists ? $this->publicUrl($value) : null,
                'size' => $exists ? $this->humanFileSize($disk->size($value)) : null,
            ];
        }

        return null;
    }

    /**
     * URL pública del archivo.
     *
     * Para el disco público usa `asset()`, o sea: relativa al **origen de la
     * página en curso**, nunca `APP_URL.'/storage/…'`. Así el preview (y el
     * logo del panel, §19.4) cargan igual con `localhost`, `127.0.0.1`, otro
     * puerto o `https`, y no hay error de CORS cuando el FileUpload vuelve a
     * pedir el archivo.
     *
     * Si algún día `settings.upload_disk` apunta a otro disco, se usa la URL
     * que ese disco declare.
     */
    protected function publicUrl(string $path): string
    {
        if ((string) config('settings.upload_disk', 'public') !== 'public') {
            return $this->disk()->url($path);
        }

        return asset('storage/'.$path);
    }

    /**
     * El disco vive en config para no tener dos fuentes de verdad: debe
     * coincidir con el ->disk() de los FileUpload de §7.3 / §7.6.
     */
    protected function disk(): Filesystem
    {
        return Storage::disk((string) config('settings.upload_disk', 'public'));
    }

    protected function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024) {
                return round($value, 1).' '.$unit;
            }

            $value /= 1024;
        }

        return round($value, 1).' TB';
    }
}
```


## 11. Vistas Blade

### 11.1 Layout principal (2 columnas)

Formulario a la izquierda (`lg:col-span-2`), preview `sticky` a la
derecha. El botón "Guardar cambios" dispara `wire:submit="save"`.

**Archivo:** `resources/views/filament/pages/manage-settings.blade.php`

```blade
<x-filament-panels::page>
    <form wire:submit="save">
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            {{-- Formulario --}}
            <div class="lg:col-span-2">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit" icon="heroicon-o-check">
                        Guardar cambios
                    </x-filament::button>
                </div>
            </div>

            {{-- Preview en vivo (solo depende de la sección "General") --}}
            <div class="lg:sticky lg:top-20">
                @include('filament.pages.partials.general-preview', [
                    'preview' => $this->getGeneralPreview(),
                ])
            </div>
        </div>
    </form>
</x-filament-panels::page>
```

> ⚠️ **Este layout necesita el theme de §15**: `grid grid-cols-1
> items-start gap-6 lg:grid-cols-3`, `lg:col-span-2`, `mt-6 flex
> justify-end` y `lg:sticky lg:top-20` son utilidades de Tailwind que el
> CSS del panel no trae. Sin el tema compilado la página sale en una sola
> columna (los datos se ven bien, sólo se pierde el reparto en dos
> columnas).
>
> **(2026-09-26) ya está compilado en este proyecto:** esas cuatro
> utilidades (`grid-cols-1`, `lg:grid-cols-3`, `lg:col-span-2`,
> `lg:sticky`) están presentes en `public/build/assets/theme-*.css`,
> verificado con `grep`. Si vuelves a ver la página apilada, repite
> `npm run build`.

### 11.2 Tarjeta de preview

Consume el array de `getGeneralPreview()`: tarjeta nativa
`x-filament::section` con badge "En vivo", la identidad — **si hay logo se
pinta el logo (y sólo él); si no, el nombre**, la misma regla que la marca
real del panel (§19.3) — con el alternado claro/oscuro entre `logo` y
`logo_dark`, favicon, contactos como badges y los badges de archivo
(`Logo · 12 KB`, `Logo (modo oscuro) · 9 KB`, `Favicon · 4 KB`). Sólo usa
componentes de Filament y clases `fi-sc-*` del CSS del panel: se ve bien sin
tema custom (§15).

**Archivo:** `resources/views/filament/pages/partials/general-preview.blade.php`

```blade
@php
    $preview ??= [];

    $logo = $preview['logo'] ?? null;
    $logoDark = $preview['logo_dark'] ?? null;
    $favicon = $preview['favicon'] ?? null;

    $hasLogo = filled($logo['url'] ?? null);
    $hasLogoDark = filled($logoDark['url'] ?? null);

    $files = array_filter([$logo, $logoDark, $favicon]);
@endphp

{{--
    Vista previa "en vivo" de la pestaña General.

    Todo se construye con componentes nativos de Filament (section, avatar,
    badge, icon) y con las clases base `fi-sc-*` que el propio CSS del panel
    ya trae: así el preview queda tematizado (claro/oscuro, tipografía, radios)
    sin necesidad de un tema custom en Vite.

    Los archivos se actualizan en cuanto se eligen porque los FileUpload de
    GeneralSettingsSchema están marcados con ->live().

    La identidad sigue la MISMA regla que la marca real del panel
    (AdminPanelProvider → §19 de la guía): si hay logo se pinta el logo —
    y sólo él —, y el nombre sólo aparece cuando no hay logo.
--}}
<x-filament::section heading="Vista previa" description="Así verán tus usuarios esta configuración." icon="heroicon-o-eye"
    divided compact>
    <x-slot:afterHeader>
        <x-filament::badge color="success" icon="heroicon-o-bolt" size="sm">
            En vivo
        </x-filament::badge>
    </x-slot:afterHeader>

    {{-- Identidad: logo (si lo hay) o nombre + favicon --}}
    <div class="fi-sc fi-inline fi-sc-has-gap fi-sc-dense">
        {{-- Logo: se alterna según el modo claro/oscuro del panel (clase
             `.dark` en <html>, la misma que usa Filament para el toggle del
             topbar): es CSS puro, así que reacciona al cambiar de tema sin
             necesidad de un ciclo Livewire. Si sólo hay un logo, ese se
             muestra en ambos modos. --}}
        @if ($hasLogo)
            <img src="{{ $logo['url'] }}" alt="{{ $logo['name'] ?? 'Logo' }}"
                @class(['flex items-center w-35  p-4 rounded-2xl', 'dark:hidden' => $hasLogoDark]) />
        @endif

        @if ($hasLogoDark)
            <img src="{{ $logoDark['url'] }}" alt="{{ $logoDark['name'] ?? 'Logo (modo oscuro)' }}"
                @class(['flex items-center w-35  p-4 rounded-2xl', 'hidden dark:block' => $hasLogo]) />
        @endif

        {{-- Sin logo se muestra la marca de agua: es el caso en el que,
             como no hay logo, va a aparecer el nombre de la aplicación. --}}
        @unless ($hasLogo || $hasLogoDark)
            <x-filament::icon icon="heroicon-o-photo" :size="\Filament\Support\Enums\IconSize::Large" class="fi-sc-icon" />
        @endunless

        <div class="fi-growable">
            <div class="fi-sc fi-inline fi-sc-has-gap fi-sc-dense">
                {{-- Misma regla que la marca real del panel (§19.3 de la guía):
                     si hay logo, el nombre NO se muestra; sólo aparece cuando
                     no hay ningún logo (claro u oscuro). --}}
                @unless ($hasLogo || $hasLogoDark)
                    <p class="fi-sc-text fi-color-neutral fi-font-semibold">
                        {{ filled($preview['site_name'] ?? null) ? $preview['site_name'] : 'Nombre de tu aplicación' }}
                    </p>
                @endunless

                @if (filled($favicon['url'] ?? null))
                    <x-filament::avatar :src="$favicon['url']" :alt="$favicon['name'] ?? 'Favicon'" :circular="false" size="sm" />
                @endif
            </div>

            @if (filled($preview['description'] ?? null))
                <p class="fi-sc-text">{{ $preview['description'] }}</p>
            @endif
        </div>
    </div>

    {{-- Contacto --}}
    @if (filled($preview['support_email'] ?? null) || filled($preview['support_phone'] ?? null))
        <div class="fi-sc fi-inline fi-sc-has-gap fi-sc-dense">
            @if (filled($preview['support_email'] ?? null))
                <x-filament::badge color="gray" icon="heroicon-o-envelope">
                    {{ $preview['support_email'] }}
                </x-filament::badge>
            @endif

            @if (filled($preview['support_phone'] ?? null))
                <x-filament::badge color="gray" icon="heroicon-o-phone">
                    {{ $preview['support_phone'] }}
                </x-filament::badge>
            @endif
        </div>
    @endif

    {{-- Archivos subidos (con nombre y tamaño) --}}
    @if ($files !== [])
        <div class="fi-sc fi-inline fi-sc-has-gap fi-sc-dense">
            @foreach ($files as $file)
                <x-filament::badge color="primary" icon="heroicon-o-paper-clip">
                    {{ $file['label'] }}{{ filled($file['size'] ?? null) ? ' · ' . $file['size'] : '' }}
                </x-filament::badge>
            @endforeach
        </div>
    @endif
</x-filament::section>
```


## 12. Uso fuera de Filament: modo mantenimiento

Ejemplo real de cómo el resto de la app consume la configuración a través
del helper `setting()`, sin depender de Filament para nada. Si
`maintenance.enabled` es `true`, muestra una vista 503 a todos los
visitantes excepto a las IPs en `maintenance.allowed_ips`, al panel de
administración y a las rutas que necesita Filament para funcionar.

**Archivo:** `app/Http/Middleware/CheckMaintenanceMode.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('maintenance.enabled', false)) {
            return $next($request);
        }

        // El panel vive en /admin, pero Livewire habla por /livewire/*
        // (updates del formulario, uploads, notificaciones) y el health
        // check de Laravel vive en /up. Si NO se excluyen, cada click del
        // panel devuelve 503 y el admin se queda bloqueado dentro del
        // mantenimiento que acaba de activar.
        if ($request->is('admin*') || $request->is('livewire/*') || $request->is('up')) {
            return $next($request);
        }

        $allowedIps = setting('maintenance.allowed_ips', []);

        if (in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'message' => setting('maintenance.message', 'Estamos en mantenimiento. Vuelve pronto.'),
        ], 503);
    }
}
```

> ⚠️ **Desde 2026-10-11 no hay UI para este middleware.** La pestaña
> *Mantenimiento* salió de `config/settings.php` (§7.7), así que
> `maintenance.enabled` sólo se puede cambiar desde fuera del panel:
> `settings_repository()->merge('maintenance', ['enabled' => true])` (o
> `put(...)`) en tinker, o un `UPDATE` directo. **Ojo con eso**: si la BD
> quedó con `enabled = true`, la web pública se sirve en 503 hasta que lo
> desactives — y recuerda que `/admin*` y `/livewire/*` están excluidos, así
> que el panel sigue funcionando para poder entrar.

> ⚠️ **Si algún día mueves el panel de ruta** (`->path('...')` en
> `AdminPanelProvider`), actualiza el `admin*` de arriba: es un prefijo de
> URL a mano, no lo infiere Laravel del panel.

**Archivo:** `resources/views/errors/maintenance.blade.php`

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mantenimiento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #0f172a;">
    <div style="text-align: center; max-width: 480px; padding: 2rem;">
        <h1 style="font-size: 1.5rem; margin-bottom: .5rem;">🛠️ En mantenimiento</h1>
        <p style="color: #475569;">{{ $message }}</p>
    </div>
</body>
</html>
```

Registro en `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\CheckMaintenanceMode::class,
    ]);
})
```


## 13. Tests

Cubre, con Pest: lectura/escritura por grupo, dot-notation con valor por
defecto, `merge()` parcial, bloqueo/desbloqueo (y que efectivamente impida
escribir), que la cache se invalide correctamente tras cada escritura, y
`flush()` sin depender de la capa Filament.

> **Lo único que ya existe y corre hoy** (actualizado 2026-10-11) es
> `tests/Feature/SettingsTabsTest.php` (§13.0): comprueba `byTab()`, que
> cada esquema ocupa su propia pestaña y que `seo`/`maintenance` ya **no**
> están registrados. No toca BD, así que **pasa sin el driver de sqlite**.
> Los cuatro ficheros de este §13 siguen pendientes.

> Cada fichero declara `uses(RefreshDatabase::class)` a nivel de archivo
> (no global: en `tests/Pest.php` está comentado a propósito).

### 13.0 El único test que corre hoy: `SettingsTabsTest`

**Archivo:** `tests/Feature/SettingsTabsTest.php` (creado 2026-09-28 y
actualizado 2026-10-11, ✅ pasa — 3 tests, 9 aserciones)

```php
<?php

use App\Filament\Settings\SettingsSchemaRegistry;

test('los esquemas se agrupan por pestaña respetando el sort', function () {
    $tabs = app(SettingsSchemaRegistry::class)->byTab();

    expect($tabs->keys()->all())
        ->toBe(['general', 'social', 'legal', 'theme'])
        ->and($tabs->get('general')->map(fn ($settingsSchema) => $settingsSchema->group())->all())
        ->toBe(['general']);
});

test('cada esquema ocupa su propia pestaña', function () {
    $registry = app(SettingsSchemaRegistry::class);

    expect($registry->find('social')->tab())->toBe('social')
        ->and($registry->find('general')->tab())->toBe('general')
        ->and($registry->find('legal')->tab())->toBe('legal')
        ->and($registry->find('theme')->tab())->toBe('theme');
});

test('seo y mantenimiento no están registrados como pestañas', function () {
    $registry = app(SettingsSchemaRegistry::class);

    expect($registry->groups())
        ->not->toContain('seo', 'maintenance')
        ->and($registry->find('seo'))->toBeNull()
        ->and($registry->find('maintenance'))->toBeNull();
});
```

Sólo pide `config()` y el contenedor, **nada de BD**, por eso corre sin
`pdo_sqlite`. El tercer test (`seo y mantenimiento no están registrados
como pestañas`) es el que blindó el cambio de 2026-10-11: si alguien
vuelve a registrar esos schemas en `config/settings.php`, el test falla.

**Archivo:** `tests/Feature/SettingsRepositoryTest.php`

```php
<?php

use App\Models\Setting;
use App\Settings\Contracts\SettingsRepository;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('guarda y lee el payload completo de un grupo', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('social', ['facebook' => 'https://facebook.com/acme']);

    expect($repo->get('social', 'facebook'))->toBe('https://facebook.com/acme');
    expect(Setting::where('group', 'social')->where('name', 'config')->exists())->toBeTrue();
});

it('lee valores anidados con dot-notation y respeta el default', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('seo', ['meta_title' => 'Mi sitio']);

    expect($repo->get('seo', 'meta_title'))->toBe('Mi sitio');
    expect($repo->get('seo', 'meta_description', 'sin descripción'))->toBe('sin descripción');
});

it('mergea parcialmente sin pisar el resto de las claves', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('general', ['site_name' => 'Acme', 'support_email' => 'a@acme.com']);
    $repo->merge('general', ['support_email' => 'nuevo@acme.com']);

    expect($repo->get('general', 'site_name'))->toBe('Acme');
    expect($repo->get('general', 'support_email'))->toBe('nuevo@acme.com');
});

it('impide escribir en un grupo bloqueado', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('legal', ['terms' => 'v1']);
    $repo->lock('legal');

    expect(fn () => $repo->put('legal', ['terms' => 'v2']))
        ->toThrow(RuntimeException::class);

    expect($repo->get('legal', 'terms'))->toBe('v1');
});

it('permite volver a escribir tras desbloquear', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('legal', ['terms' => 'v1']);
    $repo->lock('legal');
    $repo->unlock('legal');
    $repo->put('legal', ['terms' => 'v2']);

    expect($repo->get('legal', 'terms'))->toBe('v2');
});

it('invalida la cache al escribir', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('seo', ['meta_title' => 'Old']);
    expect($repo->get('seo', 'meta_title'))->toBe('Old');

    $repo->put('seo', ['meta_title' => 'New']);
    expect($repo->get('seo', 'meta_title'))->toBe('New');
});

it('invalida la cache de todos los grupos existentes con flush()', function () {
    $repo = app(SettingsRepository::class);

    $repo->put('general', ['site_name' => 'Old']);
    $repo->put('seo', ['meta_title' => 'Old']);
    expect($repo->get('general', 'site_name'))->toBe('Old');

    Setting::query()->update(['payload' => ['site_name' => 'BD', 'meta_title' => 'BD']]);

    $repo->flush();

    // Si flush() no tocará el grupo, la cache seguiría devolviendo 'Old'.
    expect($repo->get('general', 'site_name'))->toBe('BD')
        ->and($repo->get('seo', 'meta_title'))->toBe('BD');
});
```

Ejecutar:

```bash
php artisan test --filter=SettingsRepositoryTest
```

### 13.1 Preview builder

Puro, sin Livewire: valida que los archivos resueltos tengan `label`,
`name`, `url` y `size` — que es exactamente lo que consume el partial de
§11.2.

**Archivo:** `tests/Feature/SettingsPreviewBuilderTest.php`

```php
<?php

use App\Filament\Settings\SettingsPreviewBuilder;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('devuelve null para un campo vacío', function () {
    $preview = app(SettingsPreviewBuilder::class)->build([]);

    expect($preview['logo'])->toBeNull()
        ->and($preview['favicon'])->toBeNull();
});

it('resuelve nombre, url y tamaño de un archivo ya persistido', function () {
    Storage::fake('public');
    Storage::disk('public')->put('settings/logo.png', str_repeat('a', 2048));

    $preview = app(SettingsPreviewBuilder::class)->build([
        'site_name' => 'Mi sitio',
        'logo' => 'settings/logo.png',
    ]);

    expect($preview['site_name'])->toBe('Mi sitio')
        ->and($preview['logo']['label'])->toBe('Logo')
        ->and($preview['logo']['name'])->toBe('logo.png')
        ->and($preview['logo']['url'])->not->toBeNull()
        ->and($preview['logo']['size'])->toBe('2 KB');
});

it('devuelve url y size en null si el archivo no existe en el disco', function () {
    Storage::fake('public');

    $preview = app(SettingsPreviewBuilder::class)->build([
        'logo' => 'settings/no-existe.png',
    ]);

    // Este es el síntoma de un FileUpload guardado en un disco distinto
    // al que lee el builder (ver §7.3.1).
    expect($preview['logo']['name'])->toBe('no-existe.png')
        ->and($preview['logo']['url'])->toBeNull()
        ->and($preview['logo']['size'])->toBeNull();
});
```

### 13.2 Modo mantenimiento

El caso que más importa: **el mantenimiento no debe poder bloquearte a ti
mismo** dentro del panel.

**Archivo:** `tests/Feature/CheckMaintenanceModeTest.php`

```php
<?php

use App\Models\User;
use App\Settings\Contracts\SettingsRepository;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function enableMaintenance(): void
{
    app(SettingsRepository::class)->put('maintenance', [
        'enabled' => true,
        'message' => 'Volvemos pronto',
        'allowed_ips' => [],
    ]);
}

it('devuelve 503 al sitio público', function () {
    enableMaintenance();

    $this->get('/')->assertStatus(503);
});

it('no bloquea el panel de administración', function () {
    enableMaintenance();
    $this->actingAs(User::factory()->create());

    // Sin la exclusión de /admin* el admin se quedaría dentro del
    // mantenimiento que acaba de activar.
    expect($this->get('/admin')->status())->not->toBe(503);
});

it('no bloquea las peticiones de Livewire', function () {
    enableMaintenance();
    $this->actingAs(User::factory()->create());

    // /livewire/update es por donde habla el panel; si devuelve 503,
    // cada guardado del formulario de settings falla.
    expect($this->get('/livewire/update')->status())->not->toBe(503);
});

it('permite la IP autorizada', function () {
    enableMaintenance();
    app(SettingsRepository::class)->merge('maintenance', [
        'allowed_ips' => [$this->ip()],
    ]);

    $this->get('/')->assertOk();
});
```

### 13.3 La página (secciones bloqueadas)

Cierra el punto más fácil de romper de §9: `save()` debe guardar los
grupos abiertos e **ignorar + avisar** por los que están bloqueados. Da
igual que el bloque viva en un `Tab` (pestaña de un solo grupo — lo único
que hay hoy) o en una `Section` (pestaña compartida, si algún grupo vuelve
a agruparse con `tab()`): lo que corta es
`disabled()` → `isDehydrated() === false`, no el tipo de contenedor.

**Archivo:** `tests/Feature/ManageSettingsTest.php`

```php
<?php

use App\Filament\Pages\ManageSettings;
use App\Models\User;
use App\Settings\Contracts\SettingsRepository;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Ver §2.5: sin estas abilities, canAccess() devuelve false y la
    // página responde 403 para todos.
    Gate::define('manage-settings', fn (User $user): bool => true);
    Gate::define('manage-settings-lock', fn (User $user): bool => true);

    $this->actingAs(User::factory()->create());
});

it('guarda los grupos desbloqueados', function () {
    Livewire::test(ManageSettings::class)
        ->set('data.general.site_name', 'Mi sitio')
        ->call('save');

    expect(app(SettingsRepository::class)->get('general', 'site_name'))
        ->toBe('Mi sitio');
});

it('ignora y avisa por las secciones bloqueadas', function () {
    $repository = app(SettingsRepository::class);
    $repository->put('general', ['site_name' => 'Original']);
    $repository->lock('general');

    Livewire::test(ManageSettings::class)
        ->set('data.general.site_name', 'Intento de edición')
        ->call('save')
        ->assertNotified();

    expect($repository->get('general', 'site_name'))->toBe('Original');
});

it('no enseña la página a quien no tiene el permiso', function () {
    Gate::define('manage-settings', fn (User $user): bool => false);

    Livewire::test(ManageSettings::class)->assertForbidden();
});
```

> ⚠️ **Estado (2026-09-28):** el único fichero que existe es
> `tests/Feature/SettingsTabsTest.php` (§13.0, corre sin BD); **los cuatro
> de arriba no están escritos** y ni siquiera podrían ejecutarse hoy, porque
> `phpunit.xml` fuerza `DB_CONNECTION=sqlite` +
> `:memory:` pero esta instalación de PHP **no tiene `pdo_sqlite`**
> (`php -m | grep sqlite` → nada), así que cualquier test que toque BD
> falla con `could not find driver`. Antes de escribirlos: instala
> `php-sqlite3` **o** apunta `phpunit.xml` a una BD MySQL de pruebas, y
> mantén el `uses(RefreshDatabase::class)` por fichero (en `tests/Pest.php`
> está comentado a propósito).
>
> Consecuencia: al correr la suite completa hoy sale **3 passed, 1 failed**,
> y el fallo es `tests/Feature/ExampleTest` por justo ese driver, no por
> código de esta guía.

Verificación sin tests de BD, hecha contra la BD real con `Livewire::test()`
y restaurando los datos al final:

| Comprobación | Fecha | Resultado |
|---|---|---|
| `form->getState()` devuelve las 6 claves `general, social, legal, seo, maintenance, theme` | 2026-09-28 | ✅ *histórico: hoy son 4* |
| `save()` escribe `general` **y** `social` en una sola pasada (pestaña compartida) | 2026-09-28 | ✅ *histórico* |
| Con `social` bloqueada, su clave desaparece del estado y la notificación de omitida se envía, mientras `general` sí se guarda y el payload de `social` queda intacto | 2026-09-28 | ✅ |
| Pestañas renderizadas: 5 (la de *Redes Sociales* ya no existía) y las secciones *General* + *Redes Sociales* dentro de la pestaña *General* | 2026-09-28 | ✅ *histórico* |
| Tras los cambios de 2026-10-11, `byTab()` devuelve 4 pestañas `general, social, legal, theme`, cada una con un único esquema, y `registry()->groups()` no contiene `seo` ni `maintenance` | 2026-10-11 | ✅ (`SettingsTabsTest`, 3 tests) |
| `vendor/bin/pint --format agent` en los ficheros tocados | 2026-10-11 | ✅ |

Ejecutar todo:

```bash
php artisan test --compact
vendor/bin/pint --format agent    # ojo: --dirty NO sirve aquí, el proyecto no es un repo git
```


## 14. Cómo agregar una sección nueva

Ejemplo: agregar "Pagos". No se toca `ManageSettings.php` ni el
repositorio, solo se crea una clase y se registra.

**1. Crear** `app/Filament/Settings/Schemas/PaymentsSettingsSchema.php`:

```php
<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class PaymentsSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 25; // controla el orden del tab
    protected int $columns = 2;

    public function group(): string { return 'payments'; }
    public function label(): string { return 'Pagos'; }
    public function icon(): string { return 'heroicon-o-credit-card'; }

    public function schema(): array
    {
        return [
            Toggle::make('sandbox')->label('Modo pruebas'),
            TextInput::make('stripe_public_key')->label('Stripe Public Key'),
            TextInput::make('stripe_secret_key')->label('Stripe Secret Key')->password(),
        ];
    }
}
```

**2. Registrar** en `config/settings.php`:

```php
'schemas' => [
    // ...
    \App\Filament\Settings\Schemas\PaymentsSettingsSchema::class,
],
```

Listo. El tab aparece solo, con su propio estado, guardado, cache y
soporte de bloqueo — heredados gratis del resto del sistema.

> **Para meter la sección en una pestaña existente en vez de crear una
> nueva** basta con un método más (§7.1):
>
> ```php
> public function tab(): string
> {
>     return 'general'; // la pestaña de `GeneralSettingsSchema`
> }
> ```
>
> Sigue siendo un `group()` propio (`'payments'`): otra fila en la BD y
> otro candado independiente. Lo único que cambia es que `byTab()`
> agrupa ambos esquemas en la misma pestaña, que entonces se pinta sin
> `statePath` y con una `Section` por grupo (§9). Ojo con el `sort`: dentro
> de una pestaña compartida el orden de la barra lo fija **el primer
> esquema del grupo en el registry** (en la práctica el dueño) y el `sort`
> de la invitada sólo decide el orden de su bloque dentro de la pestaña.

> **Excepción: `theme` (§16).** Es la única sección cuyo valor se lee
> **desde fuera** de la página: `AdminPanelProvider` lo consume en su
> `->colors()`. El registro sigue siendo el mismo (un renglón en
> `config/settings.php`), pero además hay una clase de apoyo
> (`ThemeColorPalette`), una vista Blade y el closure del provider —
> el patrón completo está en §16.


## 15. Theming (estilo del panel)

> **✅ Hecho (2026-09-26).** Comprobado en el proyecto:
> existe `resources/css/filament/admin/theme.css` (contiene el `@import`
> del tema de Filament y los dos `@source`, incluido el de
> `resources/views/filament/**/*`), `vite.config.js` lo añade a `input` y
> `AdminPanelProvider` tiene
> `->viteTheme('resources/css/filament/admin/theme.css')`. `npm run build`
> genera `public/build/assets/theme-*.css` (≈666 kB) con las utilidades
> del layout de §11.1. Los pasos de abajo quedan como referencia para
> recrear el tema en otro proyecto.

**Desde la reescritura de §11.2 esto nunca afectó a la tarjeta de preview:**
esa está hecha sólo con componentes de Filament y clases `fi-sc-*`, que el
CSS del panel ya trae. Lo que **sí** necesitaba el theme era el layout de
§11.1 (`grid grid-cols-3`, `lg:col-span-2`, `lg:sticky` y el `mt-6 flex
justify-end` del botón "Guardar cambios"): sin compilar salía todo en una
sola columna. Ése es el motivo por el que el tema está compilado desde el
2026-09-26 — y por el que cualquier utilidad suelta que agregues a una
vista del panel necesita otro `npm run build`.

> **Por qué ocurre:** el CSS que Filament sirve por defecto
> (`public/css/filament/filament/app.css`) trae los `fi-*` de sus propios
> componentes y **ninguna utilidad de Tailwind** (verificado una a una:
> `.flex`, `.text-sm`, `.rounded-xl`, `.text-gray-500` **no** están en ese
> fichero). Cualquier clase suelta que escribas en una vista del panel
> sólo existirá cuando hayas compilado el tema de abajo. Por eso §11.2 se
> reescribió con `x-filament::section` / `avatar` / `badge` / `icon` y
> clases `fi-sc-*` (todas presentes en el CSS del panel, con sus
> variantes `dark`).

> **Y el color primario:** no se toca aquí. El color de botones, enlaces y
> sidebar activo sale de `--primary-*`, que Filament sobreescribe por request
> desde la sección *Tema del panel* — ver **§16 [Color del
> panel](#16-color-del-panel-colorthemeselector)**. Sólo ojo: el
> `@source` de `resources/views/filament/**/*` de este apartado es el que
> hace que las utilidades de la rejilla de §16.4 existan en el build.

### ⚠️ Tailwind 4: aquí no hay `tailwind.config.js`

Este proyecto usa **Tailwind CSS 4 (CSS-first)**:

- `package.json` → `tailwindcss ^4.0.0` + `@tailwindcss/vite ^4.0.0`
- `resources/css/app.css` → `@import 'tailwindcss'`, `@source`, `@theme`
- `vite.config.js` → plugin `tailwindcss()`
- **no existe ni se debe crear `tailwind.config.js`**: Tailwind 4 no lo
  lee, y crearlo no hace nada.

Por eso **no** hay que tocar nada de `content: [...]`: en v4 las rutas se
declaran con la directiva `@source` dentro del CSS, y el propio comando de
Filament las genera.

**Pasos (una sola vez por proyecto):**

```bash
php artisan make:filament-theme admin
```

El comando (verificado en `Filament\Commands\MakeThemeCommand`):

1. instala `tailwindcss` y `@tailwindcss/vite` si faltan (en este proyecto
   ya están),
2. crea `resources/css/filament/admin/theme.css` a partir de su stub:
   ```css
   @import '../../../../vendor/filament/filament/resources/css/theme.css';

   @source '../../../../app/Filament/**/*';
   @source '../../../../resources/views/filament/**/*';   // 👈 incluye el partial
   ```
3. **añade `resources/css/filament/admin/theme.css` al array `input` de
   `vite.config.js`**,
4. **añade `->viteTheme('resources/css/filament/admin/theme.css')` al
   `AdminPanelProvider`**,
5. ofrece compilar ya mismo (`npm run build`).

Si alguno de los pasos 3 o 4 no puede automatizarse, el comando avisa con
un *warning* y te dice exactamente qué línea añadir a mano. En este
proyecto ambos se aplican solos.

Verifica que quedó así y compila:

```bash
npm run build
```

> **Si la página se ve apilada en una sola columna** (preview debajo del
> formulario y botón pegado a la izquierda), casi siempre es una de estas
> dos: no se corrió `npm run build` (o `composer run dev`), o el `@source`
> del `theme.css` no cubre `resources/views/filament/**/*` — ahí viven
> `manage-settings.blade.php` y `partials/general-preview.blade.php`.


## 16. Color del panel (`ColorThemeSelector`)

Módulo **`ColorThemeSettingsSchema`** (a veces llamado *ColorThemeSelector*):
elige el color primario de todo el panel Filament a partir de las **26
paletas nativas** de `Filament\Support\Colors\Color`, lo guarda en
`settings` y lo aplica sin tocar nada más. **Sin tabla nueva ni migración**:
usa el grupo `theme` de la tabla `settings` que ya existe.

Son **seis piezas**: dos viven en `app/Filament/Settings/` y las otras cuatro
fuera de ahí.

| Pieza | Archivo | § |
|---|---|---|
| Catálogo / lista blanca de paletas | `app/Filament/Settings/ThemeColorPalette.php` | 16.2 |
| Sección del formulario | `app/Filament/Settings/Schemas/ColorThemeSettingsSchema.php` | 16.3 |
| Rejilla de swatches con el nombre de cada color | `resources/views/filament/settings/partials/theme-color-swatches.blade.php` | 16.4 |
| Registro de la sección | `config/settings.php` | 16.5 |
| Aplicar el color al panel | `app/Providers/Filament/AdminPanelProvider.php` | 16.6 |
| Recarga tras guardar | `app/Filament/Pages/ManageSettings.php::save()` | 16.7 |

### 16.1 Por qué así

- **Alcance: sólo el color `primary`.** No gray, no oscuro, no tipografía.
  `primary` es lo que Filament pinta en botones, enlaces, sidebar activo y
  cualquier elemento destacado; es donde se nota más y no hay que acertar
  con toda una paleta de grises.
- **Paletas de Filament, no hex sueltos.** Cada paleta son 11 tonos
  (`50 … 950`) ya calculados en `oklch()` con contraste correcto en modo
  claro y oscuro. Un hex a mano no da esa garantía.
- **Se lee dentro de la closure, no en el boot.** `Panel::colors()` acepta
  closures y las evalúa `Panel::getColors()`, llamado desde `Panel::boot()`,
  que ejecuta el middleware `SetUpPanel` **en cada petición del panel**. Por
  eso el sitio público y los `php artisan` no tocan la tabla `settings`.
  Detalle importante: `EvaluatesClosures::evaluate()` hace
  `$value(...$dependencies)` **sin `bindTo()`**, así que `$this` dentro de la
  closure sigue siendo `AdminPanelProvider` y `$this->primaryColorPalette()`
  funciona.
- **Vista previa = rejilla de swatches.** 26 tarjetas, cada una con el
  nombre del color y su rampa de 11 tonos; la elegida queda resaltada. Pulsar
  una tarjeta cambia el valor (`wire:click="$set(…)"`), y el `Select` de
  arriba se queda como respaldo (teclado, búsqueda, y garantía de que el
  estado existe aunque la vista falle).
- **La rejilla no es un `Field`.** `Filament\Schemas\Components\View` no
  dehidrata estado, así que `ManageSettings::save()` no la ve y no escribe un
  `swatches` fantasma en el `payload`.

### 16.2 `app/Filament/Settings/ThemeColorPalette.php`

```php
<?php

namespace App\Filament\Settings;

use Filament\Support\Colors\Color;

/**
 * Catálogo de paletas disponibles para el color primario del panel.
 *
 * Es la lista blanca que comparten dos lados que deben hablar lo mismo:
 *
 *  - `ColorThemeSettingsSchema` la usa para poblar el selector y la rejilla
 *    de vista previa.
 *  - `AdminPanelProvider` la usa para traducir el valor guardado en la paleta
 *    concreta que Filament registra como color `primary`.
 *
 * De este modo un valor manipulado a mano en la BD nunca puede romper el
 * panel: cae siempre en la paleta por defecto.
 */
class ThemeColorPalette
{
    /**
     * Paleta que se usa cuando la setting no existe o trae un valor no válido.
     * Es la que Filament trae de fábrica (`ColorManager::DEFAULT_COLORS`).
     */
    public const DEFAULT = 'amber';

    /**
     * Tonos de cada paleta, de claro a oscuro, en el mismo orden que las
     * constantes de `Color`. Todas las paletas de Filament tienen estos 11.
     *
     * @var array<int, int>
     */
    public const SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

    /**
     * Clave del selector (minúsculas) => nombre visible y constante de `Color`.
     *
     * Primero los colores cromáticos, que son los útiles como color primario;
     * al final los neutros, por si se quiere un panel sin color.
     *
     * @var array<string, string>
     */
    private const CATALOG = [
        'red' => 'Red',
        'orange' => 'Orange',
        'amber' => 'Amber',
        'yellow' => 'Yellow',
        'lime' => 'Lime',
        'green' => 'Green',
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'cyan' => 'Cyan',
        'sky' => 'Sky',
        'blue' => 'Blue',
        'indigo' => 'Indigo',
        'violet' => 'Violet',
        'purple' => 'Purple',
        'fuchsia' => 'Fuchsia',
        'pink' => 'Pink',
        'rose' => 'Rose',
        'slate' => 'Slate',
        'gray' => 'Gray',
        'zinc' => 'Zinc',
        'neutral' => 'Neutral',
        'stone' => 'Stone',
        'mauve' => 'Mauve',
        'olive' => 'Olive',
        'mist' => 'Mist',
        'taupe' => 'Taupe',
    ];

    /**
     * Opciones para el `Select`: valor en minúsculas => etiqueta.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::CATALOG;
    }

    /**
     * Normaliza cualquier valor guardado a una clave válida del catálogo.
     * Lo que no esté en la lista blanca vuelve a la paleta por defecto.
     */
    public static function key(?string $name): string
    {
        $key = strtolower(trim((string) $name));

        return array_key_exists($key, self::CATALOG) ? $key : self::DEFAULT;
    }

    /**
     * Paleta completa de Filament (11 tonos en oklch) para un nombre dado.
     *
     * @return array<int, string>
     */
    public static function palette(?string $name): array
    {
        $constant = Color::class.'::'.self::CATALOG[self::key($name)];

        /** @var array<int, string> */
        return constant($constant);
    }
}
```

**Notas:**

- `constant('Filament\Support\Colors\Color::Amber')` devuelve el array de 11
  oklch. Si la constante no existiera lanzaría `Error`, por eso `palette()`
  **siempre** pasa antes por `key()`, que ya garantiza la lista blanca.
- `key()` también normaliza mayúsculas y espacios: `"  AMBER "` → `amber`.
- Si algún día Filament renombra una paleta, se cae a `Amber` en lugar de
  romper el panel.
- Ojo con el namespace: vive en `app/Filament/Settings/`, **no** en
  `app/Settings/` (mismo aviso PSR-4 de §7).

### 16.3 `app/Filament/Settings/Schemas/ColorThemeSettingsSchema.php`

```php
<?php

namespace App\Filament\Settings\Schemas;

use App\Filament\Settings\ThemeColorPalette;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;

/**
 * Sección "Tema del panel": elige el color primario de todo el panel.
 *
 * El valor se guarda en el grupo `theme` de la tabla `settings` (una fila,
 * `name='config'`) y lo consume `AdminPanelProvider` en su `->colors()`,
 * que Filament evalúa en cada petición del panel.
 */
class ColorThemeSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 60;

    protected int $columns = 1;

    public function group(): string
    {
        return 'theme';
    }

    public function label(): string
    {
        return 'Tema del panel';
    }

    public function icon(): string
    {
        return 'heroicon-o-paint-brush';
    }

    public function schema(): array
    {
        return [
            Select::make('primary')
                ->label('Color primario')
                ->options(ThemeColorPalette::options())
                ->default(ThemeColorPalette::DEFAULT)
                ->required()
                ->live()
                ->native(false)
                ->searchable()
                ->helperText('Se aplica a botones, enlaces, sidebar y cualquier elemento activo de todo el panel. Se usa al guardar.'),

            // Rejilla de paletas con su nombre: sirve de vista previa y de
            // selector alternativo. No es un `Field`, así que no aporta
            // estado al formulario y `ManageSettings::save()` no lo ve.
            View::make('filament.settings.partials.theme-color-swatches')
                ->columnSpanFull(),
        ];
    }
}
```

**Notas:**

- `->default(ThemeColorPalette::DEFAULT)` deja `amber` (el color de fábrica
  de Filament) como primer valor: si nadie guardó nunca nada, el panel se ve
  igual que antes.
- `->live()` hace que cambiar el valor repinte la rejilla en caliente;
  `->native(false)` + `->searchable()` porque el `<select>` del navegador no
  pinta nada.
- `->required()` además evita que un `payload` sin `primary` llegue al
  repositorio.
- `columns = 1` (no dos): la rejilla ya ocupa el ancho completo con
  `->columnSpanFull()`.
- Los tres `import` del fichero real son exactamente esos. **Falta
  `Filament\Support\Colors\Color` a propósito**: las paletas las resuelve
  `ThemeColorPalette`.

### 16.4 Rejilla de swatches

`resources/views/filament/settings/partials/theme-color-swatches.blade.php`

```blade
{{-- Rejilla de paletas de Filament: vista previa y selector del color primario.
     Se renderiza desde `ColorThemeSettingsSchema` con `View::make()`, así que no
     es un `Field` y no aporta estado al formulario. --}}
@php
    // Ruta absoluta del estado de esta sección (p. ej. `data.theme`). `$get()`
    // resuelve contra el contenedor (la pestaña) y por eso es relativo; para el
    // click hace falta la ruta completa, que es la que maneja `$set()`.
    $statePath = $schemaComponent->getStatePath();
    $fieldPath = filled($statePath) ? "{$statePath}.primary" : 'primary';

    $value = $get('primary');
    $selected = \App\Filament\Settings\ThemeColorPalette::key(is_string($value) ? $value : null);
@endphp

<div class="space-y-3">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Vista previa de las paletas de Filament. La elegida aparece resaltada y
        se pinta de su tono más claro al más oscuro. Pulsa una para cambiarla;
        el color se aplica al panel al guardar.
    </p>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 2xl:grid-cols-4">
        @foreach (\App\Filament\Settings\ThemeColorPalette::options() as $key => $name)
            @php($palette = \App\Filament\Settings\ThemeColorPalette::palette($key))

            <button
                type="button"
                wire:click="$set('{{ $fieldPath }}', '{{ $key }}')"
                @class([
                    'cursor-pointer rounded-lg border p-2 text-left transition',
                    'border-primary-600 bg-primary-50/50 ring-2 ring-primary-600/40 dark:bg-primary-500/10' => $selected === $key,
                    'border-gray-200 hover:border-gray-300 dark:border-white/10 dark:hover:border-white/20' => $selected !== $key,
                ])
            >
                <span
                    @class([
                        'block text-sm',
                        'font-semibold text-primary-700 dark:text-primary-400' => $selected === $key,
                        'text-gray-700 dark:text-gray-300' => $selected !== $key,
                    ])
                >
                    {{ $name }}
                </span>

                <span class="mt-2 flex h-6 overflow-hidden rounded-md ring-1 ring-inset ring-black/5 dark:ring-white/10">
                    @foreach (\App\Filament\Settings\ThemeColorPalette::SHADES as $shade)
                        <span
                            class="h-full flex-1"
                            style="background-color: {{ $palette[$shade] }}"
                        ></span>
                    @endforeach
                </span>
            </button>
        @endforeach
    </div>
</div>
```

**Notas:**

- **De dónde salen `$schemaComponent` y `$get`.** `Component::$viewIdentifier
  = 'schemaComponent'` y `ViewComponent::renderView()` monta
  `[$viewIdentifier => $this]`; `Component::getExtraViewData()` añade además
  `get` (el `Get` utility), `operation` y `record`. `$set` **no** se inyecta
  por PHP — por eso se usa el magic action de Livewire.
- **Ruta relativa vs. absoluta.** `HasState::resolveRelativeStatePath()`
  concatena contra `getContainer()->getStatePath()`, que en una `Tab` es
  `data.theme`, así que `$get('primary')` resuelve a `data.theme.primary` y
  `$schemaComponent->getStatePath()` devuelve `data.theme`. `$set()` necesita
  la ruta completa, de ahí el `{$fieldPath}`.
- **`type="button"`** es obligatorio: sin él el click enviaría el
  `<form wire:submit="save">`.
- El **resaltado** cambia de `border-primary-600`/`ring-2` a
  `border-gray-200`, y sus clases están escritas en el propio fichero, así
  que las recoge el `@source` de §15 (hace falta `npm run build`).
- Los 11 tonos de cada tarjeta van en `style="background-color: oklch(…)"`
  **inline**, así que nunca pueden faltar del CSS compilado.

### 16.5 Registro en `config/settings.php`

Un solo renglón en el array `schemas` (fichero completo en §5):

```php
use App\Filament\Settings\Schemas\ColorThemeSettingsSchema;

// …

    'schemas' => [
        GeneralSettingsSchema::class,
        SocialSettingsSchema::class,
        LegalSettingsSchema::class,
        ColorThemeSettingsSchema::class,
    ],
```

El registry de §8 la recoge solo: la pestaña aparece sin tocar ni la página
ni el repositorio. Orden por `sort`: general (10), social (20), legal (30),
**tema (60)**. (`seo` (40) y `maintenance` (50) siguen declarados en sus
clases pero **no están en este array** desde 2026-10-11 → no generan
pestaña, §7.6 / §7.7.)

Como cada esquema tiene hoy su propia pestaña, **el panel muestra 4
pestañas** — General (10), Redes Sociales (20), Términos y Condiciones (30)
y Tema del panel (60). Si algún grupo volviera a compartir pestaña con
`tab()`, la posición en la barra la fija el `sort()` del **dueño** de esa
pestaña (§7.1 / §14).

### 16.6 Aplicar el color: `AdminPanelProvider`

Sustituye el `->colors(['primary' => Color::Amber])` original (y su
`use Filament\Support\Colors\Color;`, que ya no se usa directamente):

```php
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // … resto idéntico: default(), id(), path(), viteTheme(), login()…
            ->colors(fn (): array => [
                'primary' => $this->primaryColorPalette(),
            ])
            // Identidad del panel (§19): nombre, logo claro, logo en modo
            // oscuro, favicon y destino del enlace del logo. También closures,
            // por exactamente el mismo motivo que ->colors().
            ->brandName(fn (): ?string => $this->settingValue('general.site_name'))
            ->brandLogo(fn (): ?string => $this->generalAssetUrl('logo'))
            ->darkModeBrandLogo(fn (): ?string => $this->generalAssetUrl('logo_dark'))
            ->favicon(fn (): ?string => $this->generalAssetUrl('favicon'))
            ->homeUrl(fn (): ?string => $this->settingValue('general.site_url'))
            // … discoverResources / pages / widgets / middleware / authMiddleware …
            ->authMiddleware([
                Authenticate::class,
            ]);
        // ->plugins([
        //     MiaTheme::make()
        //         ->accentColor('#C9A227')
        //         ->secondaryColor('#E8C4C0')
        //         ->font('Jost', 'Cormorant Garamond')
        //         ->roundness('soft')
        //         ->density('comfortable')
        //         ->elevation(0.75)
        //         ->motion()
        //         ->darkMode(),
        // ]);
    }

    /**
     * Paleta del color primario del panel, tomada de la sección
     * "Tema del panel" (grupo `theme` de la tabla `settings`).
     *
     * Se resuelve dentro de la closure que recibe `->colors()` porque ahí es
     * donde Filament registra los colores: en `Panel::boot()`, que se ejecuta
     * en el middleware `SetUpPanel` de cada petición del panel. Así el sitio
     * público y los comandos de consola no tocan la tabla `settings`.
     *
     * @return array<int, string>
     */
    protected function primaryColorPalette(): array
    {
        try {
            $name = setting('theme.primary', ThemeColorPalette::DEFAULT);
        } catch (\Throwable) {
            // Si la tabla `settings` todavía no existe (primer arranque,
            // migraciones a medias) el panel no debe caerse por un color.
            $name = ThemeColorPalette::DEFAULT;
        }

        return ThemeColorPalette::palette(is_string($name) ? $name : null);
    }
}
```

**Cómo llega al navegador:**

1. `SetUpPanel` llama a `Panel::boot()` en cada petición `/admin/**`.
2. `FilamentColor::register($this->getColors())` evalúa la closure → lee
   `theme.primary` (una query indexada o un cache hit, nunca ambas) y
   devuelve la paleta de 11 tonos.
3. `FilamentAsset::renderStyles()` emite un `<style>` en el `<head>` con
   `--primary-50 … --primary-950`.
4. El CSS compilado **no** define `--color-primary-*` (esa clave sale de
   `var(--primary-*)` en `vendor/filament/support/resources/css/index.css`),
   así que el bloque inline no choca con nada y gana sin `!important`.

> ⚠️ **Mantén comentado el bloque `// ->plugins([MiaTheme::make() …])`.**
> Está fuera a propósito (AGENTS.md): activo, lanza
> `ParseError: syntax error, unexpected token ";"` **en el boot** y toda la
> app devuelve 500 con el ruido engañoso `Target class [view] does not exist`.

### 16.7 Recarga tras guardar: `ManageSettings::save()`

Al final de `save()`, después de las notificaciones y de `mount()`:

```php
        // §16: los colores del panel se imprimen en el <head> durante el render
        // de la página, así que Livewire no puede pintarlos en caliente. Se
        // recarga la misma URL para que un cambio de color se vea en el acto.
        // Las notificaciones viven en la sesión (`session()->pull()`), así que
        // sobreviven a la recarga.
        $this->redirect(url()->previous(route('filament.admin.pages.manage-settings')));
```

- `url()->previous()` lee la cabecera `Referer` de la petición XHR de
  Livewire — que es la propia página de ajustes, con su `?tab=theme` — y, si
  no viniera, `_previous.url` de la sesión; el `route()` es el último
  recurso.
- **Sin este redirect el cambio "no se ve":** el usuario pulsa *Guardar*,
  recibe la notificación verde y el panel sigue con el color viejo hasta que
  navega a otro sitio. Es el síntoma clásico de "no se aplica".
- Afecta a **todos** los grupos, no sólo a `theme`, y es inofensivo para
  ellos: `mount()` relee la BD y la recarga vuelve a montar el form.

### 16.8 Verificación realizada (2026-09-27)

| Qué se probó | Cómo | Resultado |
|---|---|---|
| Registro de la sección | `SettingsSchemaRegistry::groups()` | *histórico (2026-09-27):* `general, social, legal, seo, maintenance, theme` — *desde 2026-10-11 son sólo `general, social, legal, theme`* ✅ |
| Normalización | `ThemeColorPalette::key()` | `"noexiste"` → `amber`, `"AMBER "` → `amber` ✅ |
| Paleta | `ThemeColorPalette::palette('violet')` | 11 tonos, `600 = oklch(0.541 0.281 293.009)` ✅ |
| Render de la rejilla | `Livewire::test(ManageSettings::class)->html()` | 26 `$set('data.theme.primary', …)` distintos, **286** estilos (`26 × 11`), los 26 nombres visibles, pestaña *Tema del panel* ✅ |
| Resaltado de la elegida | búsquedas en el HTML | `amber` con `border-primary-600`; `teal` sin él ✅ |
| Color aplicado | `FilamentColor::register($panel->getColors())` | sin setting → `oklch(0.666 0.179 58.318)` (Amber); `violet` → `oklch(0.541 0.281 293.009)`; `zzz` → vuelve a Amber ✅ |
| Estilo inline real | `FilamentAsset::renderStyles()` con `violet` | `--primary-500:oklch(0.606 0.25 292.717); --primary-600:oklch(0.541 0.281 293.009); --primary-700:oklch(0.491 0.27 292.581)` ✅ |
| Guardar | `->set('data.theme.primary', 'violet')->call('save')` | fila `theme` con `{"primary":"violet"}` y `assertRedirectContains('manage-settings')` ✅ |
| Assets | `npm run build` + `grep` en `theme-*.css` | están `2xl:grid-cols-4`, `sm:grid-cols-3`, `.ring-primary-600\/40`, `.bg-primary-50\/50`, `.dark:bg-primary-500\/10`, `.dark\:border-white\/10` ✅ |
| Sin regresiones | `curl /`, `/admin/login`, `/admin` | `200`, `200`, `302` (esperado: sin sesión) ✅ |
| Formateo | `vendor/bin/pint --format agent <rutas>` | `passed` ✅ |
| BD restaurada | se borró la fila `theme` creada de prueba | quedan sólo `general, legal, maintenance, seo, social` ✅ *histórico: los grupos `seo`/`maintenance` siguen en la BD aunque ya no tengan pestaña* |

**No se pudo ejecutar la suite de tests**: `phpunit.xml` fuerza sqlite y esta
compilación PHP no tiene `pdo_sqlite` (§13). Si quieres cubrir el módulo con
tests, decide primero a qué BD de prueba apuntar.

### 16.9 Cuidados y trampas

- **`npm run build` obligatorio** tras cualquier cambio de este módulo.
  Tailwind 4 es CSS-first y sólo recoge lo que está en el `@source` de §15;
  sin build, la rejilla se ve sin grid ni bordes y parece rota.
- **Sin migración.** El payload es `{"primary":"violet"}` en la fila
  `group='theme'`, `name='config'`. Si borras la fila, el panel sigue
  funcionando con `Amber`.
- **El `catch (\Throwable)` es deliberado.** Sólo protege la *lectura* del
  color: si la tabla no existe, el admin sigue arrancando. No lo conviertas
  en un catch silencioso más amplio.
- **Si cambias la ruta del panel** (`->path('admin')`), cambia también la
  ruta del `route()` de §16.7 — el middleware de §12 tiene el mismo
  acoplamiento.
- **La pestaña se bloquea como las demás:** `ManageSettings` la deshabilita
  si haces `lock('theme')`, y entonces su contenido desaparece del
  `getState()`.
- **Añadir otro rol** (gray, oscuro, tipografía…) sigue el mismo patrón: una
  clave en `ThemeColorPalette`, un campo en el schema y una línea en la
  closure de `->colors()` (p. ej. `'gray' => ThemeColorPalette::palette('zinc')`).
- **`Str::sanitizeHtml()` no interviene aquí.** Sólo aplica a componentes que
  emiten HTML guardado (`TextEntry::html()`, `RichEditor`); esta rejilla es
  una vista de Filament renderizada en el servidor. Que el sanitizador
  permita `class` y `style` sí es lo que permitiría reusar estos mismos
  estilos dentro de un `TextEntry::->html()`.


## 17. Notas y decisiones de diseño

- **Cache:** `forever`, invalidada automáticamente en `put`, `merge`,
  `lock` y `unlock`. Desactivable con `SETTINGS_CACHE_ENABLED=false` en
  `.env` (útil en `local` si estás iterando rápido sobre valores por
  defecto). El store por defecto del proyecto es `database`; se puede
  cambiar con `SETTINGS_CACHE_STORE`.
- **Bloqueo (`locked`):** vive en la fila de cada grupo. `put()` lanza
  `RuntimeException` si el grupo está bloqueado. **La página NO hace
  `try/catch`:** en su lugar pre-consulta `isLocked()` en el `foreach` de
  `save()` y salta el grupo, y además detecta por diferencia los que
  Filament ya excluyó del estado (bloques deshabilitados: `Tab` de una
  sola sección o `Section` de una pestaña compartida) — ambos casos
  terminan en la misma notificación amarilla, sin romper el guardado de
  las demás secciones.
- **`tab()` ≠ `group()` (unión visual, no migración):** `HasSettingsSchema::tab()`
  existe para mostrar varios grupos en la misma pestaña en vez de meter los
  campos de uno dentro del otro. Se usó de 2026-09-28 a 2026-10-11 para
  pintar *Redes Sociales* dentro de *General*, y **desde ese día ya ningún
  grupo lo usa** (cada esquema tiene pestaña propia) — pero el mecanismo
  sigue vivo: la rama de pestaña compartida de `ManageSettings::form()`
  (sin `statePath`, una `Section` por grupo, §9) se conserva para poder
  volver a agrupar sin tocar la página. Es una decisión deliberada:
  **la BD, `setting('social.*')`, los `casts` y los candados no cambian**,
  sólo dónde se pinta cada grupo.
- **XSS en `RichEditor`:** los campos `terms`, `privacy_policy` y
  `cookies_policy` guardan **HTML crudo**. Si el sitio público los imprime
  con `{!! !!}`, sanitiza antes (p. ej. `Purifier`/`HTMLPurifier`). Con
  `{{ }}` se escapan y se verían las etiquetas en texto plano.
- **`preventFilePathTampering()` (recomendación `Danger` de la docs de
  Filament 5):** el valor de un `FileUpload` es un string controlado por
  el cliente, así que una request manipulada puede apuntar a **cualquier
  archivo del mismo disco**. Aquí el formulario es de admin autenticado y
  todo vive en `settings/`, así que el riesgo es bajo. ⚠️ Si algún día lo
  activas, ten en cuenta lo que dice la propia docs: **"needs a record on
  the form"** — en un form sin modelo (como este) haría fallar la
  validación de todas las rutas ya guardadas, salvo que apruebes cada una
  con el callback `allowFilePathUsing`. Las subidas nuevas no se ven
  afectadas. `->preventFilePathTampering()` está en
  `Filament\Forms\Components\BaseFileUpload`.
- **Disco de subida:** `FILESYSTEM_DISK=local` (privado) es el default del
  proyecto; por eso los `FileUpload` llevan `->disk('public')` y el
  builder lee `config('settings.upload_disk')`. Recuerda
  `php artisan storage:link` (§2.2).
- **Multi-tenant:** si tu app maneja múltiples tenants, agrega una
  columna `tenant_id` a la tabla y súmala al `cacheKey()` de
  `EloquentSettingsRepository` (es el único punto que tocarías: query +
  cache key).
- **Por qué el modelo no tiene lógica:** para poder reusarlo en
  contextos donde no quieres cache ni las reglas de bloqueo (ej. un
  comando de importación masiva), sin arrastrar comportamiento
  inesperado.
- **Por qué el preview es un builder aparte y no un método privado en la
  página:** para poder testearlo sin bootear todo Livewire/Filament, y
  para que el patrón se repita igual cuando agregues más previews.
- **Por qué la tarjeta de preview usa componentes de Filament y clases
  `fi-sc-*` en vez de utilidades de Tailwind:** con utilidades sueltas la
  tarjeta sale sin estilo **y sin modo oscuro** (el CSS del panel no las
  tiene, §15). Con `x-filament::section` (tarjeta, cabecera, divisores),
  `x-filament::avatar` (logo y favicon reales), `x-filament::badge`
  (contacto, tamaño de archivo, "En vivo") y `x-filament::icon` (marca de
  agua cuando no hay logo), el preview se ve correcto en claro y oscuro
  **sin compilar el tema**. El layout interno usa sólo `fi-sc`,
  `fi-inline`, `fi-sc-has-gap`, `fi-sc-dense`, `fi-growable`, `fi-sc-text`,
  `fi-color-neutral`, `fi-font-semibold` y `fi-sc-icon` — verificadas una
  a una en `public/css/filament/filament/app.css`, con sus variantes
  `:where(.dark, .dark *)`.
- **Sin `SoftDeletes`:** ver la nota de §3 (rompería el
  `updateOrCreate` + unique contra filas trashadas).


## 18. Pendientes y decisiones abiertas

Puntos que esta guía **deja resueltos de una forma concreta pero
revisable**, por si tu caso real es distinto:

1. **Política de permisos.** Las abilities `manage-settings` y
   `manage-settings-lock` se registran con `fn () => true` (§2.5), o sea:
   *cualquier usuario autenticado* entra a la página y puede bloquear.
   Si hay roles o un admin único, cámbialas ahí — `canAccess()` y
   `visible()` ya apuntan a esas abilities, no hay que tocar más nada.
2. **`softDeletes()`** está fuera de la tabla a propósito (§3). Si lo
   reactivas, el repositorio debe pasar a `withTrashed()` +
   `restore()`.
3. **Nombre global `setting()`.** Hay un guard `function_exists`, pero el
   nombre es genérico y puede colisionar con otro paquete. Alternativa:
   llamarla `settings()` / `settings_key()`; sólo hay que cambiar
   `app/helpers.php` y sus llamadas (§12).
4. **Disco por defecto de Filament.** Sigue en `local` porque
   `FILESYSTEM_DISK=local`. Los `FileUpload` lo anulan con
   `->disk('public')`. Si prefieres un solo punto de configuración, pon
   `FILESYSTEM_DISK=public` en `.env` (en Filament v5 **no** existe
   `FILAMENT_FILESYSTEM_DISK`: el config es
   `filament.default_filesystem_disk = env('FILESYSTEM_DISK', 'local')`)
   y **quita** los `->disk()` explícitos. Ojo: eso cambia el disco por
   defecto de toda la app.
5. **Rutas del panel.** El middleware de mantenimiento asume `/admin`
   (§12). Si cambias `->path()` en `AdminPanelProvider`, cámbialo ahí.
6. **Tests de §13 sin crear.** Bloqueo real: `phpunit.xml` fuerza sqlite
   `:memory:` y esta PHP no tiene `pdo_sqlite`. Decide primero si instalas
   `php-sqlite3` o mueves los tests a MySQL, y después escribe los cuatro
   ficheros de §13 con su `uses(RefreshDatabase::class)` de cabecera.
7. **Theme de §15 — ✅ hecho (2026-09-26).** `make:filament-theme admin`
   creó `resources/css/filament/admin/theme.css`, `vite.config.js` lo añadió
   a `input` y el provider lleva `->viteTheme(...)`; `npm run build` compila
   las utilidades del layout de §11.1 (verificado con `grep` en el CSS
   compilado). Del tema ya sólo queda **mantener** `npm run build` tras
   cualquier cambio de vistas.
8. **PSR-4.** La ruta del fichero tiene que casar con el namespace: un
   desajuste no lanza error, sólo `class_exists() === false` en caliente
   (`composer dump-autoload` avisa — mira los avisos, no los ignores).
9. **CORS de las URLs de `storage` — decidido dejarlo fuera (2026-09-28).**
   El logo, el favicon y el preview ya salen **same-origin** gracias a
   `asset()` (§19.4), pero el `FileUpload` sigue pidiendo sus miniaturas con
   `fetch()` sobre la URL del **disco** (`APP_URL.'/storage/…'`), así que si
   abres el panel desde un origen distinto de `APP_URL` la consola sigue
   mostrando *"Solicitud desde otro origen bloqueada …"* y la miniatura no
   se pinta. Arreglo de una línea en `config/filesystems.php:44`
   (`'url' => rtrim(env('ASSET_URL', ''), '/').'/storage'`) — pendiente de
   decidir.
10. **SEO y Mantenimiento sin UI (decidido el 2026-10-11).** Las dos
    pestañas salieron del panel quitando sus clases de `config/settings.php`
    (§7.6 / §7.7); los datos de la BD siguen ahí y el middleware y el
    partial de SEO siguen leyéndolos. Si en algún momento vuelven a
    necesitarse desde la UI, basta con re-registrar las clases (una línea
    cada una + sus `use`) y `php artisan config:clear`. Mientras tanto,
    activar el mantenimiento o retocar el SEO exige tinker/BD — y el test
    de §13.0 falla a propósito si alguien las registra sin pensarlo.
11. **Bloque TEMPORAL de diagnóstico en `save()` (§9).** El `Log::info` con
    `$bdAntes`/`bd_despues` sigue en `ManageSettings.php` pendiente de
    confirmarse la causa de "no se guarda en la BD"; en cuanto se cierre el
    incidente, bórralo (escribe el volcado de la tabla en cada guardado).

---

## 19. Identidad aplicada al panel (nombre, logo, favicon, home)

La pestaña **General** no es sólo "datos del sitio": sus cinco primeros
campos **personalizan el panel de administración**. Todo se enchufa en
`AdminPanelProvider` con closures, exactamente igual que el color de §16.
**Sin migración nueva y sin tocar ni el repositorio ni la página**: los
valores ya viven en el grupo `general` (§7.3).

### 19.1 Qué controla qué

| Campo (`general.*`) | Método de Filament v5 | Dónde se ve |
|---|---|---|
| `site_name` | `->brandName()` | nombre de la marca, `alt` de la imagen y sufijo del `<title>` (`Login - Caryn Harrison`) |
| `logo` | `->brandLogo()` | `<img class="fi-logo fi-logo-light">` en sidebar, topbar y página de login |
| `logo_dark` | `->darkModeBrandLogo()` | `<img class="fi-logo fi-logo-dark">`, en el mismo sitio |
| `favicon` | `->favicon()` | `<link rel="icon">` en el `<head>` de las páginas del panel |
| `site_url` | `->homeUrl()` | `href` del enlace que envuelve el logo |

Los cinco métodos aceptan `Closure` y viven en
`vendor/filament/filament/src/Panel/Concerns/` (`HasBrandName`,
`HasBrandLogo`, `HasFavicon`, `HasRoutes`). `Filament\Panel` los usa todos
(verificado en `Panel.php:20,21,29`).

### 19.2 Código

**Archivo:** `app/Providers/Filament/AdminPanelProvider.php`

Dentro de `panel()` (junto a `->colors()`, §16.6):

```php
            ->colors(fn (): array => [
                'primary' => $this->primaryColorPalette(),
            ])
            // Identidad del panel, tomada de la pestaña "General" (grupo
            // `general` de la tabla `settings`): nombre, logo claro, logo en
            // modo oscuro, favicon y el destino del enlace del logo.
            //
            // Van como closures por el MISMO motivo que ->colors(): Filament
            // las guarda en el Panel y las evalúa al renderizar (getBrandName,
            // getBrandLogo, getDarkModeBrandLogo, getFavicon, getHomeUrl),
            // nunca durante el boot. Así el sitio público y los `php artisan`
            // no tocan la tabla `settings`. Ver §19 de la guía.
            ->brandName(fn (): ?string => $this->settingValue('general.site_name'))
            ->brandLogo(fn (): ?string => $this->generalAssetUrl('logo'))
            ->darkModeBrandLogo(fn (): ?string => $this->generalAssetUrl('logo_dark'))
            ->favicon(fn (): ?string => $this->generalAssetUrl('favicon'))
            ->homeUrl(fn (): ?string => $this->settingValue('general.site_url'))
```

Y los dos helpers al final de la clase:

```php
    /**
     * Valor de texto de una setting, tolerante a una tabla que todavía no
     * existe (primer arranque, migraciones a medias) y a valores vacíos.
     *
     * Devuelve `null` cuando no hay valor: Filament trata `null` como
     * "no configurado" y cae a su default (`config('app.name')` en
     * `brandName()`, `getUrl()` en `homeUrl()`).
     */
    protected function settingValue(string $key): ?string
    {
        try {
            $value = setting($key);
        } catch (\Throwable) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    /**
     * URL pública de un archivo subido desde la pestaña "General":
     * `logo`, `logo_dark` o `favicon`.
     *
     * Devuelve `null` si la ruta no está en `settings/` o si el archivo no
     * existe en el disco: así, sin logo, la marca del panel se ve con el
     * nombre en texto en lugar de una imagen rota (misma regla que §19.3).
     *
     * Usa `asset()` y no `Storage::url()`: la URL nace del origen de la
     * página en curso, así carga igual en `localhost`, `127.0.0.1`, otro
     * puerto o `https`. `Storage::url()` devuelve `APP_URL.'/storage/…'`
     * (origen fijo), que es lo que el navegador bloquea por CORS cuando el
     * panel se abre desde otro host (ver §19.4).
     */
    protected function generalAssetUrl(string $field): ?string
    {
        $path = $this->settingValue('general.'.$field);

        if ($path === null || ! str_starts_with($path, 'settings/')) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path);
    }
```

### 19.3 La regla: el logo manda, el nombre es el respaldo

La lógica ya está escrita en Filament
(`vendor/filament/filament/resources/views/components/logo.blade.php`):

- `brandLogo` con URL → imprime el `<img>`;
- `brandLogo` **vacío** → imprime **el `$brandName` como texto** (y si
  `brandName` también es `null`, Filament cae a `config('app.name')`,
  ver `HasBrandName::getBrandName()`);
- si existe `darkModeBrandLogo`, imprime **los dos** `<img>` con las clases
  `fi-logo-light` y `fi-logo-dark`.

El alternado es **CSS puro**, ya compilado en `public/build/assets/theme-*.css`:

```css
.fi-logo.fi-logo-light:where(.dark,.dark *), .fi-logo.fi-logo-dark { display: none }
.fi-logo.fi-logo-dark:where(.dark,.dark *) { display: flex }
```

O sea: al pulsar el toggle de tema del topbar cambia el logo **sin petición
y sin ciclo Livewire** (misma técnica que el preview de §11.2).

La tarjeta de preview (§11.2) aplica **la misma regla**: si hay logo se pinta
el logo — y sólo él —; el nombre sólo aparece cuando no hay ningún logo.

> **Sin logo y sin nombre:** el panel se ve con `config('app.name')`, nunca
> roto. `generalAssetUrl()` devuelve `null` si el archivo no existe en el
> disco, así que el `<img>` no se emite.

### 19.4 Por qué `asset()` y no `Storage::url()`

- `Storage::disk('public')->url($path)` lee
  `config('filesystems.disks.public.url')` = **`APP_URL.'/storage/…'`** →
  origen fijo (`http://localhost:8000`).
- `asset('storage/'.$path)` usa la raíz de **la petición en curso** → el
  origen desde el que el navegador está viendo la página.

Por qué importa: el `FileUpload` de Filament vuelve a pedir esos archivos
con `fetch()` (`vendor/filament/forms/resources/js/components/file-upload.js:248`)
para pintar la miniatura. Si el panel se abre en un origen distinto de
`APP_URL` (`127.0.0.1` en vez de `localhost`, otro puerto, un túnel https),
el navegador lo bloquea con *"Solicitud desde otro origen bloqueada: la
política de mismo origen impide leer el recurso remoto … falta la cabecera
CORS `Access-Control-Allow-Origin`"* y la miniatura no aparece. Con
`asset()` el logo, el favicon y el preview salen siempre **same-origin**.

Verificado (2026-09-28) contra `http://127.0.0.1:8000/admin/login`:
`src="http://127.0.0.1:8000/storage/settings/…"`, **no** `localhost:8000`.

> **Decisión (2026-09-28): quedó fuera el cambio de `config/filesystems.php`.**
> Esas mismas URLs absolutas las genera el **disco** para el propio
> FileUpload (`'url' => …APP_URL…'/storage'`) y ahí no interviene
> `asset()`, así que los avisos de CORS en la consola siguen ahí cuando el
> origen no coincide con `APP_URL`. Si algún día quieres eliminarlos del
> todo: `'url' => rtrim(env('ASSET_URL', ''), '/').'/storage'` (con
> `ASSET_URL` vacío queda `/storage`, relativo) en `config/filesystems.php:44`
> — una sola línea, sin efectos secundarios porque esas URLs sólo las usa
> el navegador.

### 19.5 Por qué closures y no una lectura directa

`panel()` se ejecuta en el **boot** de cada petición, incluidas las públicas
y las de consola. Leer `setting()` ahí haría una query (o un cache hit) en
`/`, en cada `php artisan …` y en los tests. Con closures, Filament sólo
las llama desde `getBrandName()`, `getBrandLogo()`, `getDarkModeBrandLogo()`,
`getFavicon()` y `getHomeUrl()`, todos invocados **al pintar el panel**.
Mismo argumento que §16.1.

`EvaluatesClosures::evaluate()` **no hace `bindTo()`**, así que `$this`
dentro de la closure sigue siendo `AdminPanelProvider` y los helpers se
llaman igual que `primaryColorPalette()`.

### 19.6 Cuidados y trampas

- **Tabla inexistente:** `settingValue()` envuelve `setting()` en
  `catch (\Throwable)` (mismo patrón que `primaryColorPalette()`) y devuelve
  `null` → el panel arranca con `config('app.name')` y sin logo, nunca 500.
- **Guard de ruta:** `generalAssetUrl()` sólo acepta rutas que empiezan por
  `settings/` y comprueba `exists()`. Sin eso, un `payload` manipulado
  podría colar una URL externa en la marca del panel.
- **`->homeUrl()` sólo cambia el enlace del logo.** Únicos consumidores en
  el vendor: `livewire/sidebar.blade.php:91` y `livewire/topbar.blade.php:120`.
  Si `site_url` está vacío, Filament vuelve a `getUrl()` (el dashboard).
- **El favicon del panel NO afecta al sitio público:** `<link rel="icon">`
  lo emite el layout de Filament sólo en `/admin/**`. Para la web pública
  está el partial de §20.
- **Si cambias la ruta del panel** (`->path('admin')`), nada de lo anterior
  se rompe (no hay rutas hardcodeadas), pero sí el middleware de §12.
- **`npm run build` no hizo falta para este módulo**: no se añadieron clases
  nuevas; las utilidades del preview (`dark:hidden`, `hidden dark:block`) se
  compilaron el 2026-09-28 junto con el resto.

### 19.7 Verificación (2026-09-28)

| Qué se probó | Cómo | Resultado |
|---|---|---|
| Syntax + formato | `php -l` y `vendor/bin/pint --format agent` | sin errores, `passed` ✅ |
| Closures evaluadas | `Filament::getBrandName()/getBrandLogo()/getDarkModeBrandLogo()/getFavicon()/getHomeUrl()` | `Caryn Harrison`, las dos URLs de logo, el favicon y `https://www.xafesyj.ca` ✅ |
| Página real | `curl http://127.0.0.1:8000/admin/login` | `<title> Login - Caryn Harrison`, dos `<img class="fi-logo fi-logo-light/dark">` con `src` del **origen de la petición** y `<link rel="icon">` ✅ |
| Preview con logo | render del partial con `build(setting('general'))` | pinta los `<img>`, **no** pinta el nombre ✅ |
| Preview sin logo | `build(['site_name' => 'Mi Empresa'])` | pinta el nombre ✅ |
| Preview vacío | `build([])` | placeholder `Nombre de tu aplicación` ✅ |
| Blade | `php artisan view:cache` | compila sin errores ✅ |

---

## 20. Consumir los settings en plantillas Blade (ejemplos)

Los settings **no son exclusivos de Filament**: el helper `setting()` (§6)
lee desde cualquier vista, controlador, job o middleware del sitio público.
Este apartado es un **catálogo de recetas** — copia la que necesites.

Recordatorio rápido de la API (§6):

```php
setting('general.site_name');              // valor puntual (dot notation)
setting('general');                         // payload completo del grupo
setting('general.site_name', 'Mi sitio');   // con valor por defecto
setting('social', []);                      // grupo entero o array vacío
```

### 20.1 Head SEO completo: título, meta, favicon, Open Graph y Analytics

**Archivo nuevo:** `resources/views/partials/seo-head.blade.php`

Consume los grupos `seo` y `general`, aplica los defaults (si no hay
`meta_title` usa `site_name`; si no hay `meta_description` usa
`general.description`) y respeta el toggle `indexable` de la pestaña SEO:

```blade
{{--
    Cabecera SEO del sitio público.

    Consume los grupos `seo` y `general` de la tabla `settings` con el helper
    global `setting()` (§6 de la guía), así que no depende de Filament para
    nada. Se incluye una sola vez dentro del <head> de cada plantilla pública:

        <head>
            @include('partials.seo-head')
        </head>

    Aporta: <title>, meta description/keywords/robots, canonical, favicon,
    Open Graph + Twitter Card y Google Analytics.
--}}
@php
    /** Archivo subido desde settings: acepta string o array (estado de FileUpload). */
    $settingAsset = function (mixed $path): ?string {
        $path = is_array($path) ? \Illuminate\Support\Arr::first($path) : $path;

        return is_string($path) && filled($path) ? asset('storage/'.$path) : null;
    };

    $siteName = (string) setting('general.site_name', config('app.name'));
    $siteUrl = (string) setting('general.site_url', '');
    $title = (string) setting('seo.meta_title', $siteName);
    $description = (string) setting('seo.meta_description', (string) setting('general.description', ''));
    $keywords = (string) setting('seo.meta_keywords', '');
    $canonical = (string) setting('seo.canonical_url', $siteUrl !== '' ? $siteUrl : url()->current());
    $indexable = (bool) setting('seo.indexable', true);
    $ogImage = $settingAsset(setting('seo.og_image'));
    $favicon = $settingAsset(setting('general.favicon'));
    $analyticsId = (string) setting('seo.google_analytics_id', '');
@endphp

<title>{{ filled($title) ? $title : config('app.name') }}</title>

@if (filled($description))
    <meta name="description" content="{{ $description }}">
@endif

@if (filled($keywords))
    <meta name="keywords" content="{{ $keywords }}">
@endif

{{-- Sin indexación cuando la pestaña SEO la tiene desactivada. --}}
<meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $canonical }}">

@if (filled($favicon))
    <link rel="icon" href="{{ $favicon }}">
@endif

{{-- Open Graph / Twitter: lo que se pinta al compartir en redes (§7.6). --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:url" content="{{ $canonical }}">

@if (filled($description))
    <meta property="og:description" content="{{ $description }}">
@endif

@if (filled($ogImage))
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ filled($ogImage) ? 'summary_large_image' : 'summary' }}">

{{-- Google Analytics (medición de la pestaña SEO) --}}
@if (filled($analyticsId))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @js($analyticsId));
    </script>
@endif
```

Se incluye **una sola vez** dentro del `<head>` de cada plantilla pública.
En `resources/views/welcome.blade.php` reemplaza el `<title>` de fábrica:

```blade
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Título, meta SEO, favicon, Open Graph y Analytics desde settings (§20) --}}
        @include('partials.seo-head')
```

> **Verificado (2026-09-28)** con `curl http://127.0.0.1:8000/` sobre los
> datos reales de la BD: `<title>` con el `meta_title` de la pestaña SEO,
> `meta description`, `<meta name="robots" content="noindex, nofollow">`
> (la pestaña SEO lo tiene desactivado), `canonical` = `site_url`,
> `og:site_name` = `site_name`, `og:url`, `og:description`, favicon y
> `twitter:card`.

### 20.2 Cabecera del sitio público: logo o nombre (misma regla que §19.3)

```blade
<header>
    @if (filled($logo = setting('general.logo')))
        <img src="{{ asset('storage/'.$logo) }}"
             alt="{{ setting('general.site_name', config('app.name')) }}"
             height="40">
    @else
        <span>{{ setting('general.site_name', config('app.name')) }}</span>
    @endif
</header>
```

Para que también cambie con el modo oscuro, sube `logo_dark` (§7.3) y
alterna con clases de Tailwind — misma técnica que §11.2:

```blade
<img src="{{ asset('storage/'.setting('general.logo')) }}" class="dark:hidden" alt="">
<img src="{{ asset('storage/'.setting('general.logo_dark')) }}" class="hidden dark:block" alt="">
```

> Fíjate en `asset('storage/'.…)`: **relativo al origen de la página**, no
> `APP_URL` (§19.4).

### 20.3 Descripción y contacto (grupo `general`)

```blade
<p>{{ setting('general.description') }}</p>

<a href="mailto:{{ setting('general.support_email') }}">
    {{ setting('general.support_email') }}
</a>

<a href="tel:{{ setting('general.support_phone') }}">
    {{ setting('general.support_phone') }}
</a>
```

### 20.4 Redes sociales (grupo `social`) en el footer

```blade
<ul class="flex gap-4">
    @foreach (setting('social', []) as $red => $url)
        @if (filled($url) && $red !== 'whatsapp')
            <li>
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                    {{ ucfirst($red) }}
                </a>
            </li>
        @endif
    @endforeach

    @if (filled($wa = setting('social.whatsapp')))
        <li>
            {{-- `whatsapp` guarda un teléfono (+51999999999), no una URL --}}
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}"
               target="_blank" rel="noopener noreferrer">WhatsApp</a>
        </li>
    @endif
</ul>
```

### 20.5 Términos y privacidad (grupo `legal`)

```blade
<nav>
    <a href="{{ route('legal.terms') }}">Términos y condiciones</a>
    <a href="{{ route('legal.privacy') }}">Política de privacidad</a>
    <a href="{{ route('legal.cookies') }}">Política de cookies</a>
</nav>
```

Y la aceptación explícita en el formulario de registro:

```blade
@if (setting('legal.require_acceptance', true))
    <label>
        <input type="checkbox" name="accept_terms" required>
        He leído y acepto los
        <a href="{{ route('legal.terms') }}" target="_blank">términos</a>
        y la <a href="{{ route('legal.privacy') }}" target="_blank">política de privacidad</a>.
    </label>
@endif
```

> ⚠️ **`terms`, `privacy_policy` y `cookies_policy` guardan HTML crudo** del
> `RichEditor`. Si los imprimes con `{!! !!}` **sanifícalos antes**
> (`Purifier` / `HTMLPurifier`); con `{{ }}` se escapan y se verían las
> etiquetas en texto plano. Ver §17.

### 20.6 Lo que YA está implementado (no lo dupliques)

| Grupo | Dónde se consume hoy |
|---|---|
| `maintenance` | `CheckMaintenanceMode` (§12) — middleware, no vista — *⚠ sin UI desde 2026-10-11 (§7.7)* |
| `theme` | `AdminPanelProvider::colors()` (§16) — sólo el panel |
| `general` (marca) | `AdminPanelProvider` (§19) — sólo el panel; el partial de §20.1 lleva favicon y SEO a la web |
| `seo` | `resources/views/partials/seo-head.blade.php` (§20.1) — *⚠ sin UI desde 2026-10-11 (§7.6): con la fila vacía el partial cae a los defaults (`site_name`, `general.description`, indexable)* |
| `social`, `legal` | **sin consumidor todavía** — recetas §20.4 y §20.5 |

### 20.7 Cuidados

- **Cache:** cada `setting()` es una lectura con `rememberForever` sobre el
  store por defecto (`database`). Varias llamadas a la **misma clave** son
  varios *hits* de la misma fila, pero si vas a usar muchos campos seguidos
  agrúpalos: `setting('general')` una vez y array.
- **Defaults siempre:** escribe `setting('x', $default)`. La tabla puede
  estar vacía (otro esquema, primer arranque) y no quieres un `null` en el
  `<title>`.
- **No leas settings en el boot** de providers, middleware globales ni en
  scripts de `composer.json`: si la tabla no existe, la app no arranca.
  Usa closures dentro del render (§19.5) o envuelve en `try/catch` como hace
  `primaryColorPalette()`.
- **Si agregas una sección nueva** (§14), este apartado es donde documentas
  cómo se consume en Blade.
