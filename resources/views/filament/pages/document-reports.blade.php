<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="updateStats">
            {{ $this->form }}
        </form>

        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-lg">
                    <x-filament::icon
                        icon="heroicon-o-funnel"
                        class="w-6 h-6 text-primary-600 dark:text-primary-400"
                    />
                </div>
                <div>
                    <h3 class="text-lg font-bold">Resumen de búsqueda</h3>
                    <p class="text-sm text-gray-500">{{ $totalFound }} documentos encontrados con los filtros actuales.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-eye"
                    wire:click="generatePreview"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="generatePreview">Vista Previa</span>
                    <span wire:loading wire:target="generatePreview">Generando...</span>
                </x-filament::button>

                <x-filament::button
                    color="primary"
                    icon="heroicon-o-arrow-down-tray"
                    wire:click="download"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="download">Descargar PDF</span>
                    <span wire:loading wire:target="download">Procesando...</span>
                </x-filament::button>
            </div>
        </div>

        @if($previewUrl)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                    <h3 class="font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-gray-400" />
                        Vista Previa del Reporte
                    </h3>
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-o-x-mark"
                        wire:click="$set('previewUrl', null)"
                    >
                        Cerrar
                    </x-filament::button>
                </div>
                <div class="w-full" style="height: 800px;">
                    <iframe
                        src="data:application/pdf;base64,{{ $previewUrl }}"
                        class="w-full h-full border-none"
                    ></iframe>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
