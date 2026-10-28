<?php

namespace App\Livewire\Custom;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Livewire\Concerns\HasTenantMenu;
use Filament\Livewire\Concerns\HasUserMenu;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomTopBar extends Component implements HasActions, HasSchemas
{
    use HasTenantMenu, HasUserMenu, InteractsWithActions, InteractsWithSchemas;

    #[On('refresh-topbar')]
    public function refreshTopbar(): void {}

    public function render()
    {
        return view('livewire.custom.custom-top-bar');
    }
}
