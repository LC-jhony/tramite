<?php

namespace App\Livewire;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Priority;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
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
                Section::make(__('Register external procedure'))
                    ->description(__('Fill in the required fields to register your procedure'))
                    ->schema([
                        Fieldset::make(__('Procedure data'))
                            ->schema([
                                Select::make('customer_id')
                                    ->label(__('Client'))
                                    ->options(fn () => Customer::all()->pluck('full_name', 'id'))
                                    ->searchable(['dni', 'full_name'])
                                    ->required()
                                    ->native(false),

                                TextInput::make('document_number')
                                    ->label(__('Document number'))
                                    ->prefix('EXT-')
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::getNextSequentialNumber('document_number');
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('case_number')
                                    ->label(__('Case number'))
                                    ->prefix('EXP-')
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::caseNumber();
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('priority_id')
                                    ->label(__('Priority'))
                                    ->options(fn () => Priority::where('status', true)->pluck('name', 'id'))
                                    ->default(fn () => Priority::where('name', 'Media')->value('id'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden(),
                                Select::make('origen')
                                    ->options([
                                        'Interno' => __('Internal'),
                                        'Externo' => __('External'),
                                    ])
                                    ->default('Externo')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('status')
                                    ->enum(DocumentStatus::class)
                                    ->default(DocumentStatus::REGISTERED)
                                    ->disabled()
                                    ->dehydrated(),
                                Textarea::make('subject')
                                    ->label(__('Subject'))
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('document_type_id')
                                    ->label(__('Document type'))
                                    ->options(fn () => DocumentType::where('status', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->native(false)
                                    ->required()
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
                                Select::make('area_origen_id')
                                    ->label(__('Origin area'))
                                    ->options(fn () => Office::where('status', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->native(false),
                                Select::make('gestion_id')
                                    ->label(__('Management'))
                                    ->options(fn () => Administration::where('status', true)->pluck('name', 'id'))
                                    ->default(fn () => Administration::where('status', true)->value('id'))
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('folio')
                                    ->label(__('Folio'))
                                    ->required(),
                                DatePicker::make('reception_date')
                                    ->label(__('Reception date'))
                                    ->default(now())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('response_deadline')
                                    ->label(__('Response deadline'))
                                    ->disabled()
                                    ->dehydrated(),

                            ])->columnSpan(2),
                        Fieldset::make(__('Documents'))
                            ->schema([
                                AdvancedFileUpload::make('files')
                                    ->label(__('Upload files'))
                                    ->storeFiles(false)
                                    ->multiple()
                                    ->directory('documents')
                                    ->visibility('public')
                                    ->maxFiles(5)
                                    ->maxSize(10240)
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'image/jpeg',
                                        'image/png',
                                    ])
                                    ->required()
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->helperText(__('Allowed file types'))
                                    ->afterStateHydrated(function (AdvancedFileUpload $component, $state, $record) {
                                        if ($record && $record instanceof Document && $record->exists) {
                                            $filePaths = $record->files->pluck('path')->toArray();
                                            $component->state($filePaths);
                                        }
                                    }),

                            ])->columnSpan(2),
                        Checkbox::make('condition')
                            ->label(__('Terms acceptance'))
                            ->required()
                            ->default(true)
                            ->dehydrated()
                            ->rule('required')
                            ->columnSpanFull(),
                    ])->columns(4)
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model(Document::class);
    }

    private static function getNextSequentialNumber(string $field): string
    {
        $year = now()->year;
        $prefix = "{$year}-";
        $lastDocument = Document::where($field, 'like', "{$prefix}%")
            ->orderBy($field, 'desc')
            ->first();
        $lastNumber = $lastDocument
            ? (int) str_replace($prefix, '', $lastDocument->$field)
            : 0;
        $nexNumber = $lastNumber + 1;

        return sprintf('%s%04d', $prefix, $nexNumber);
    }

    private static function caseNumber(): string
    {
        $year = now()->year;

        // Generate a 5-digit random number that always starts with 0 (00001–09999)
        $sequence = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $randomNumber = "0{$sequence}";

        return "{$year}-{$randomNumber}";
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
