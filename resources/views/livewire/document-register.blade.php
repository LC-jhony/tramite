<x-container>
 <form wire:submit="create">
        {{ $this->form }}

        <x-filament::button type="submit" class='mt-4'>
            Registrar tramite
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</x-container>
