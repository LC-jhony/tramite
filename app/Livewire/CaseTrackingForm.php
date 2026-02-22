<?php

namespace App\Livewire;

use Dom\Text;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CaseTrackingForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('case_information')
                    ->label('Case Information')
                    ->components([
                        TextInput::make('case_number')
                            ->label('Case Number')
                            ->required()
                            ->placeholder('Enter the case number'),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->required()
                            ->placeholder('Enter the DNI'),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        //
    }

    public function render(): View
    {
        return view('livewire.case-tracking-form');
    }
}
