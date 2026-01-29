<?php

namespace App\Livewire;

use App\Models\Document;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DocumentForm extends Component implements HasActions, HasSchemas
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
                Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->default(null),
                TextInput::make('document_number')
                    ->required(),
                TextInput::make('case_number')
                    ->required(),
                TextInput::make('subject')
                    ->required(),
                TextInput::make('origen')
                    ->required(),
                Select::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->required(),
                Select::make('area_origen_id')
                    ->relationship('areaOrigen', 'name')
                    ->required(),
                Select::make('gestion_id')
                    ->relationship('gestion', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                TextInput::make('folio')
                    ->default(null),
                DatePicker::make('reception_date')
                    ->required(),
                DatePicker::make('response_deadline'),
                TextInput::make('condition')
                    ->default(null),
                TextInput::make('status')
                    ->required(),
                TextInput::make('priority_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('id_office_destination')
                    ->numeric()
                    ->default(null),
            ])
            ->statePath('data')
            ->model(Document::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = Document::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.document-form');
    }
}
