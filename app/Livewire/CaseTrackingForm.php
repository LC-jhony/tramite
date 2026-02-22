<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Document;
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

    public ?Document $document = null;

    public ?string $error = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('case_information')
                    ->label(__('Search case'))
                    ->schema([
                        TextInput::make('case_number')
                            ->label(__('Case number'))
                            ->required()
                            ->placeholder(__('Enter case number')),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->required()
                            ->placeholder(__('Enter DNI'))
                            ->afterStateUpdated(fn () => $this->searchDocument()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function searchDocument(): void
    {
        $this->error = null;
        $this->document = null;

        $caseNumber = $this->data['case_number'] ?? null;
        $dni = $this->data['dni'] ?? null;

        if (empty($caseNumber) || empty($dni)) {
            return;
        }

        $customer = Customer::where('dni', $dni)->first();

        if (! $customer) {
            $this->error = __('No client found with that DNI');

            return;
        }

        $this->document = Document::where('case_number', $caseNumber)
            ->where('customer_id', $customer->id)
            ->with(['movements.originOffice', 'movements.destinationOffice', 'movements.originUser', 'movements.destinationUser', 'documentType', 'priority'])
            ->first();

        if (! $this->document) {
            $this->error = __('No document found with that case number and DNI');
        }
    }

    public function submit(): void
    {
        $data = $this->form->getState();
    }

    public function render(): View
    {
        return view('livewire.case-tracking-form');
    }
}
