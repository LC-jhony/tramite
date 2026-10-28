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
