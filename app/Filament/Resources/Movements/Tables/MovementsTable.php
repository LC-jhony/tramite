<?php

namespace App\Filament\Resources\Movements\Tables;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Movement;
use App\Models\Office;
use App\Models\User;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->columns([
                TextColumn::make('document.document_number')
                    ->label('Nº Documento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('originOffice.name')
                    ->label('Oficina Origen')
                    ->searchable()
                    ->color('success')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('originUser.name')
                    ->label('Usuario Origen')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('action')
                    ->label('Acción')
                    ->searchable()
                    ->badge()
                    ->color(fn (MovementAction $state): ?string => $state->getColor()),
                TextColumn::make('destinationOffice.name')
                    ->label('Oficina Destino')
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('destinationUser.name')
                    ->label('Usuario Destino')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('receipt_date')
                    ->label('Fecha de Recepción')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable()
                    ->color(fn (MovementStatus $state): ?string => $state->getColor()),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                self::getForwardAction(),
                self::getRejectAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
                        fn (callable $get) => $get('destination_office_id')
                            ? User::where('office_id', $get('destination_office_id'))
                                ->pluck('name', 'id')
                            : []
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn (Get $get) => filled($get('destination_office_id')))
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
                    ->maxSize(10240) // Tamaño máximo en KB (10 MB
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
                            ->viewData(fn ($record) => [
                                'documentId' => $record->document_id,
                            ])->columnSpanFull(),
                    ]),
            ])
            ->action(function (Movement $record, array $data) {
                $document = $record->document;

                DB::transaction(function () use ($document, $record, $data) {
                    $record->update([
                        'status' => MovementStatus::COMPLETED,
                    ]);

                    $document->movements()->create([
                        'document_id' => $document->id,
                        'origin_office_id' => Auth::user()->office_id,
                        'origin_user_id' => Auth::id(),
                        'destination_office_id' => $data['destination_office_id'],
                        'destination_user_id' => $data['destination_user_id'],
                        'action' => MovementAction::DERIVACION,
                        'indication' => $data['indication'] ?? null,
                        'receipt_date' => now(),
                        'status' => MovementStatus::PENDING,
                    ]);
                    $document->update([
                        'status' => DocumentStatus::IN_PROCESS,
                        'id_office_destination' => $data['destination_office_id'],
                    ]);
                    if (isset($data['attached_files']) && ! empty($data['attached_files'])) {
                        foreach ($data['attached_files'] as $filePath) {
                            if (Storage::exists($filePath)) {
                                $fileName = basename($filePath);
                                $mimeType = Storage::mimeType($filePath);
                                $size = Storage::size($filePath);

                                $document->files()->create([
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
            });
        // ->disabled(fn($record) => $record->status !== DocumentStatus::REGISTERED);
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
            ->modalDescription('¿Estás seguro de que deseas rechazar este movimiento?')
            ->form([
                TextInput::make('document_id')
                    ->label('Caso')
                    ->default(fn ($record) => $record?->document?->case_number)
                    ->disabled()
                    ->dehydrated(false),
                RichEditor::make('observation')
                    ->label('Observación')
                    ->required()
                    ->placeholder('Escriba aquí la observación del rechazo')
                    ->columnSpanFull(),
            ])
            ->action(function (Movement $record, array $data) {
                $document = $record->document;

                DB::transaction(function () use ($document, $record, $data) {
                    $record->update([
                        'status' => MovementStatus::REJECTED,
                        'observation' => $data['observation'],
                    ]);

                    $document->movements()->create([
                        'document_id' => $document->id,
                        'origin_office_id' => Auth::user()->office_id ?? $record->origin_office_id,
                        'origin_user_id' => Auth::id(),
                        'destination_office_id' => $record->origin_office_id,
                        'destination_user_id' => $record->origin_user_id,
                        'action' => MovementAction::RECHAZADO,
                        'indication' => null,
                        'observation' => $data['observation'],
                        'receipt_date' => now(),
                        'status' => MovementStatus::PENDING,
                    ]);
                    $document->update([
                        'status' => DocumentStatus::REJECTED,
                    ]);
                });
            })
            ->disabled(function (Movement $record): bool {
                return $record->status !== MovementStatus::PENDING
                    || Auth::id() !== $record->destination_user_id
                    || $record->document->status === DocumentStatus::REJECTED;
            });
    }
}
