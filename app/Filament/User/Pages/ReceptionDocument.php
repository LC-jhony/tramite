<?php

namespace App\Filament\User\Pages;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceptionDocument extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.user.pages.reception-document';

    protected static string|BackedEnum|null $navigationIcon = 'carbon-logical-partition';

    protected static ?string $title = 'Recepcion Documentos';

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->query(
                Document::query()
                    ->with(['latestMovement.originOffice', 'latestMovement.destinationOffice', 'areaOrigen'])
                    ->whereHas('latestMovement', function ($query) {
                        $query->where('destination_office_id', Auth::user()->office_id)
                            ->whereIn('status', [MovementStatus::PENDING, MovementStatus::RECEIVED]);
                    })
            )
            ->columns([
                TextColumn::make('case_number')
                    ->label('Número de Caso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('latestMovement.originOffice.name')
                    ->label('Oficina Origen')
                    ->badge()
                    ->color('info'),
                TextColumn::make('latestMovement.action')
                    ->label('Acción')
                    ->badge()
                    ->color(fn(MovementAction $state): string => $state->getColor()),
                TextColumn::make('latestMovement.receipt_date')
                    ->label('Fecha de Derivación')
                    ->date()
                    ->sortable(),
                TextColumn::make('latestMovement.status')
                    ->label('Estado del Movimiento')
                    ->badge()
                    ->color(fn(MovementStatus $state): string => $state->getColor()),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
            ])
            ->recordActions([
                self::getReceiveAction(),
                self::getForwardAction(),
                self::getRejectAction(),
                ViewAction::make(),
            ]);
    }

    private static function getReceiveAction(): Action
    {
        return Action::make('receive')
            ->label('Recepcionar')
            ->button()
            ->color('primary')
            ->icon(Heroicon::Inbox)
            ->requiresConfirmation()
            ->modalIcon(Heroicon::CheckCircle)
            ->modalDescription('¿Confirma la recepción de este documento?')
            ->action(function (Document $record) {
                DB::transaction(function () use ($record) {
                    // Actualizar el último movimiento a RECEIVED
                    $latestMovement = $record->latestMovement;
                    if ($latestMovement && $latestMovement->status === MovementStatus::PENDING) {
                        $latestMovement->update([
                            'status' => MovementStatus::RECEIVED,
                        ]);
                    }
                });
            })
            ->visible(function (Document $record): bool {
                $latestMovement = $record->latestMovement;

                return $latestMovement
                    && $latestMovement->status === MovementStatus::PENDING
                    && $latestMovement->destination_office_id === Auth::user()->office_id;
            })
            ->successNotificationTitle('Documento recepcionado correctamente');
    }

    private static function getForwardAction(): Action
    {
        return Action::make('derivar')
            ->label('Derivar')
            ->color('success')
            ->icon(Heroicon::RocketLaunch)
            ->requiresConfirmation()
            ->modalIcon(Heroicon::Envelope)
            ->modalWidth(Width::TwoExtraLarge)
            ->modalDescription('Derivar el documento a otra oficina o usuario.')
            ->slideOver()
            ->form([
                Select::make('destination_office_id')
                    ->label('Oficina de Destino')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $user = User::where('office_id', $state)->first();
                        $set('destination_user_id', $user?->id);
                    }),
                Select::make('destination_user_id')
                    ->label('Usuario de Destino')
                    ->options(
                        fn(callable $get) => $get('destination_office_id')
                            ? User::where('office_id', $get('destination_office_id'))
                            ->pluck('name', 'id')
                            : []
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn(Get $get) => filled($get('destination_office_id')))
                    ->helperText('Opcional: selecciona un usuario específico')
                    ->disabled()
                    ->dehydrated(),

                Textarea::make('indication')
                    ->label('Indicación'),
                Textarea::make('observation')
                    ->label('Observación'),
                DatePicker::make('receipt_date')
                    ->label('Fecha de recepción')
                    ->default(now())
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                AdvancedFileUpload::make('attached_files')
                    ->label('Archivos Adjuntos')
                    ->multiple()
                    ->directory('documents')
                    ->visibility('public')
                    ->maxFiles(5)
                    ->maxSize(10240) // Tamaño máximo en KB (10 MB)
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->downloadable()
                    ->openable()
                    ->previewable()
                    ->reorderable()
                    ->columnSpanFull()
                    ->helperText('Tipos de archivo permitidos: PDF, Word, Excel, JPG, PNG. Tamaño máximo por archivo: 10 MB.'),

                Fieldset::make('Archivos adjuntos')
                    ->columnSpanFull()
                    ->schema([
                        View::make('pages.file-view')
                            ->viewData(fn($record) => [
                                'documentId' => $record->id,
                            ])->columnSpanFull(),
                    ]),
            ])
            ->action(function (Document $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    // Marcar el movimiento actual como completado
                    $latestMovement = $record->latestMovement;
                    if ($latestMovement) {
                        $latestMovement->update([
                            'status' => MovementStatus::COMPLETED,
                        ]);
                    }

                    // Crear nuevo movimiento de derivación
                    $record->movements()->create([
                        'document_id' => $record->id,
                        'origin_office_id' => Auth::user()->office_id,
                        'origin_user_id' => Auth::id(),
                        'destination_office_id' => $data['destination_office_id'],
                        'destination_user_id' => $data['destination_user_id'],
                        'action' => MovementAction::DERIVACION,
                        'indication' => $data['indication'] ?? null,
                        'receipt_date' => now(),
                        'status' => MovementStatus::PENDING,
                    ]);

                    // Actualizar documento
                    $record->update([
                        'status' => DocumentStatus::IN_PROCESS,
                        'id_office_destination' => $data['destination_office_id'],
                    ]);

                    // Guardar archivos adjuntos
                    if (isset($data['attached_files']) && ! empty($data['attached_files'])) {
                        foreach ($data['attached_files'] as $filePath) {
                            if (Storage::exists($filePath)) {
                                $fileName = basename($filePath);
                                $mimeType = Storage::mimeType($filePath);
                                $size = Storage::size($filePath);

                                $record->files()->create([
                                    'filename' => $fileName,
                                    'path' => $filePath,
                                    'mime_type' => $mimeType,
                                    'size' => $size,
                                    'uploaded_by' => Auth::id(),
                                ]);
                            }
                        }
                    }
                });
            })
            ->visible(function (Document $record): bool {
                $latestMovement = $record->latestMovement;

                return $latestMovement
                    && $latestMovement->status === MovementStatus::RECEIVED
                    && $latestMovement->destination_office_id === Auth::user()->office_id;
            })
            ->successNotificationTitle('Documento derivado correctamente');
    }

    private static function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Rechazar')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->requiresConfirmation()
            ->modalWidth(Width::TwoExtraLarge)
            ->modalIcon(Heroicon::CircleStack)
            ->modalDescription('¿Estás seguro de que deseas rechazar este documento?')
            ->form([
                TextInput::make('document_id')
                    ->label('Caso')
                    ->default(fn($record) => $record?->case_number)
                    ->disabled()
                    ->dehydrated(false),
                RichEditor::make('observation')
                    ->label('Observación')
                    ->required()
                    ->placeholder('Escriba aquí la observación del rechazo')
                    ->columnSpanFull(),
            ])
            ->action(function (Document $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    // Marcar el movimiento actual como rechazado
                    $latestMovement = $record->latestMovement;
                    if ($latestMovement) {
                        $latestMovement->update([
                            'status' => MovementStatus::REJECTED,
                            'observation' => $data['observation'] ?? null,
                        ]);
                    }

                    // Crear movimiento de rechazo devolviendo a la oficina de origen
                    $record->movements()->create([
                        'document_id' => $record->id,
                        'origin_office_id' => Auth::user()->office_id,
                        'origin_user_id' => Auth::id(),
                        'destination_office_id' => $latestMovement->origin_office_id,
                        'destination_user_id' => $latestMovement->origin_user_id,
                        'action' => MovementAction::RECHAZADO,
                        'observation' => $data['observation'] ?? null,
                        'receipt_date' => now(),
                        'status' => MovementStatus::PENDING,
                    ]);

                    // Actualizar documento
                    $record->update([
                        'status' => DocumentStatus::REJECTED,
                        'id_office_destination' => $latestMovement->origin_office_id,
                    ]);
                });
            })
            ->visible(function (Document $record): bool {
                $latestMovement = $record->latestMovement;

                return $latestMovement
                    && in_array($latestMovement->status, [MovementStatus::PENDING, MovementStatus::RECEIVED])
                    && $latestMovement->destination_office_id === Auth::user()->office_id;
            })
            ->successNotificationTitle('Documento rechazado correctamente');
    }
}
