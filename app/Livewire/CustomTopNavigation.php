<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Facades\Filament;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomTopNavigation extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions, InteractsWithSchemas;

    #[On('refresh-topbar')]
    public function refresh(): void {}

    public function getUserMenuItems(): array
    {
        return Filament::getUserMenuItems();
    }

    public function getNavigation(): array
    {
        return Filament::getNavigation();
    }

    public function render()
    {
        return view('livewire.custom-top-navigation');
    }
}
