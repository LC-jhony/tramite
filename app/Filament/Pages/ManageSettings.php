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
