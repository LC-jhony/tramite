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
