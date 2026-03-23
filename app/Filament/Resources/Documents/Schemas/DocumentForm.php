<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Priority;
use App\Models\User;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registrar Documento')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        Fieldset::make('Documento')
                            ->schema([
                                TextInput::make('document_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::getNextSequentialNumber('document_number');
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('case_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return DocumentForm::caseNumber();
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                RichEditor::make('subject')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('user_id')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->default(Auth::id())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('origen')
                                    ->required()
                                    ->default('Interno')
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('current_office_id')
                                    ->options(Office::where('status', true)->pluck('name', 'id'))
                                    ->default(Auth::user()->office_id)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('gestion_id')
                                    ->options(Administration::where('status', true)->pluck('name', 'id'))
                                    ->default(function () {
                                        return Administration::where('status', true)->value('id');
                                    })
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('reception_date')
                                    ->default(now())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('response_deadline')
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
                                    ->options(DocumentStatus::class)
                                    ->default(DocumentStatus::Registrado->value)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('folio')
                                    ->required()
                                    ->numeric(),

                                Select::make('priority_id')
                                    ->options(Priority::where('status', true)->get()->mapWithKeys(static fn($priority) => [
                                        $priority->id => "<span class='flex items-center gap-x-4'>
                                                        <span class='rounded-full w-4 h-4' style='background:{$priority->color}'></span>
                                                        <span>{$priority->name}</span>
                                                        </span>",
                                    ]))
                                    ->allowHtml()
                                    ->native(false),
                            ])
                            ->columnSpan(2),
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
                            ])
                            ->columnSpan(2),
                    ]),
                Checkbox::make('condition')
                    ->label('Acepto que todo acto administrativo derivado del presente procedimiento se me
                                notifique a mi correo electrónico ( Numeral 4 del artículo 20° del Texto Único
                                Ordenado de la Ley N° 27444 )')
                    ->required()
                    ->default(true)
                    ->dehydrated()
                    ->rule('required')
                    ->columnSpanFull(),
            ]);
    }
    public static function getNextSequentialNumber(string $field): string
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

    public static function caseNumber(): string
    {
        $year = now()->year;

        // Generate a 5-digit random number that always starts with 0 (00001–09999)
        $sequence = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $randomNumber = "0{$sequence}";

        return "{$year}-{$randomNumber}";
    }
}
