<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Livewire\Concerns\HasTenantMenu;
use Filament\Livewire\Concerns\HasUserMenu;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomTopNavigation extends Component implements HasActions, HasSchemas
{
    use HasTenantMenu;
    use HasUserMenu;
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
