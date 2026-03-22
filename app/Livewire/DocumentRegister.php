<?php

namespace App\Livewire;

use App\Enum\DocumentStatus;
use App\Filament\User\Resources\Documents\Schemas\DocumentForm;
use App\Mail\RegisterDocumentCustomer;
use App\Models\Administration;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Priority;
use App\Trait\HasFileUploads;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class DocumentRegister extends Component implements HasActions, HasSchemas
{
    use HasFileUploads, InteractsWithActions, InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registrar Documento de Mesa de Partes')
                    ->description('Complete el formulario para registrar un nuevo documento en mesa de partes.')
                    ->schema([
                        Fieldset::make('Documento')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Usuario')
                                    ->options(fn() => $this->getCustomerOptions())
                                    ->getSearchResultsUsing(fn(string $search) => $this->searchCustomers($search))
                                    ->searchable()
                                    ->createOptionForm(self::customerForm())
                                    ->preload()
                                    ->native(false)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('document_number')
                                    ->label('Numero del tramite')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::getNextSequentialNumber('document_number');
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('case_number')
                                    ->label('Numero tramite')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::caseNumber();
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                RichEditor::make('subject')
                                    ->label('Asunto')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('origen')
                                    ->label('Origen')
                                    ->required()
                                    ->default('Externo')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('current_office_id')
                                    ->label('Oficina')
                                    ->options(Office::where('status', true)->pluck('name', 'id'))
                                    ->default(Office::where('name', 'Mesa de partes')->value('id'))
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->rules(['exists:offices,id']),
                                Select::make('gestion_id')
                                    ->label('Gestión')
                                    ->options(Administration::where('status', true)->pluck('name', 'id'))
                                    ->default(function () {
                                        return Administration::where('status', true)->value('id');
                                    })
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('reception_date')
                                    ->label('Fecha recepcion')
                                    ->default(now())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('response_deadline')
                                    ->label('Dias de Respuesta')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('document_type_id')
                                    ->label('Documento tipo')
                                    ->options(DocumentType::where('status', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (! $state) {
                                            $set('response_deadline', null);

                                            return;
                                        }
                                        $type = DocumentType::find($state);
                                        $days = ($type && $type->response_days) ? $type->response_days : 7;
                                        $set('response_deadline', now()->addDays($days)->toDateString());
                                    }),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options(DocumentStatus::class)
                                    ->default(DocumentStatus::Registrado->value)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('folio')
                                    ->label('Folio')
                                    ->required()
                                    ->numeric(),
                                Select::make('priority_id')
                                    ->options(Priority::where('status', true)->pluck('name', 'id'))
                                    ->default(Priority::where('name', 'Media')->value('id'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden(),
                            ])->columnSpan(2),
                        Fieldset::make('Documento')
                            ->schema([

                                AdvancedFileUpload::make('file_upload')
                                    ->label('Adjuntar archivo')
                                    ->multiple()
                                    ->directory('documents')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->pdfToolbar(true)
                                    ->pdfZoomLevel(100)
                                    ->pdfPreviewHeight(600)
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->columnSpanFull(),
                            ])->columnSpan(2),
                        Checkbox::make('condition')
                            ->label('Acepto que todo acto administrativo derivado del presente procedimiento se me
                                notifique a mi correo electrónico ( Numeral 4 del artículo 20° del Texto Único
                                Ordenado de la Ley N° 27444 )')
                            ->required()
                            ->default(true)
                            ->dehydrated()
                            ->rule('required')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->prepareDocumentData();

        DB::transaction(function () use ($data) {
            $document = Document::create($data);
            HasFileUploads::syncFilesStatic($document, $data['file_paths'] ?? []);

            $customer = Customer::find($document->customer_id);
            if ($customer && $customer->email) {
                Mail::to($customer->email)->send(new RegisterDocumentCustomer($document, $customer));
            }
        });

        Notification::make()
            ->title('Documento registrado exitosamente.')
            ->body('Se ha enviado un correo de confirmación.')
            ->success()
            ->send();

        $this->reset();
        $this->form->fill();
    }

    private function prepareDocumentData(): array
    {
        $data = $this->form->getState();

        // Extract and remove file paths
        $data['file_paths'] = HasFileUploads::getUploadedPathsStatic($data, 'file_upload');
        unset($data['file_upload']);

        return $data;
    }

    private function customerForm(): array
    {
        return [
            Toggle::make('representation')
                ->required()
                ->label('Representación'),
            TextInput::make('full_name')
                ->label('Nombre Completo')
                ->required()
                ->maxLength(255),
            TextInput::make('first_name')
                ->label('Nombres')
                ->maxLength(100),
            TextInput::make('last_name')
                ->label('Apellidos')
                ->maxLength(100),
            TextInput::make('dni')
                ->label('DNI')
                ->numeric()
                ->length(8)
                ->maxLength(8),
            TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(20),
            TextInput::make('email')
                ->label('Correo Electrónico')
                ->email()
                ->maxLength(255),
            TextInput::make('address')
                ->label('Dirección')
                ->maxLength(255),
            TextInput::make('ruc')
                ->label('RUC')
                ->numeric()
                ->length(11)
                ->maxLength(11),
            TextInput::make('company')
                ->label('Empresa')
                ->maxLength(255),
        ];
    }

    private function getCustomerOptions(): array
    {
        return Customer::query()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn($customer) => $this->formatCustomerOption($customer))
            ->toArray();
    }

    private function searchCustomers(string $search): array
    {
        return Customer::where(
            fn($query) => $query
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('dni', 'like', "%{$search}%")
                ->orWhere('ruc', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
        )
            ->limit(50)
            ->get()
            ->mapWithKeys(fn($customer) => $this->formatCustomerOption($customer))
            ->toArray();
    }

    private function formatCustomerOption(Customer $customer): array
    {
        $identifier = $customer->dni ? "DNI: {$customer->dni}" : "RUC: {$customer->ruc}";

        return [$customer->id => "{$customer->full_name} ({$identifier})"];
    }

    public function render()
    {
        return view('livewire.document-register');
    }
}
