<div>
    <form wire:submit="submit">
        {{ $this->form }}

        <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg">
            Submit
        </button>
    </form>

    <x-filament-actions::modals />
</div>
