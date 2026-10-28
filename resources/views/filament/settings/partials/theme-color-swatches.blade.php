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
