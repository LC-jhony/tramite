<?php

namespace App\Filament\User\Resources\Documents\Tables;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->columns([
                TextColumn::make('document_number')
                    ->label('Numero'),
                TextColumn::make('case_number')
                    ->label('Caso'),

                TextColumn::make('origen')
                    ->label('Origén')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Interno' => 'info',
                        'Externo' => 'danger',
                    }),
                TextColumn::make('areaOrigen.name')
                    ->label('Oficina')
                    ->badge()
                    ->color(function (string $state): string {
                        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'gray'];
                        $index = abs(crc32($state)) % count($colors);
                        return $colors[$index];
                    }),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('N/A'),
                TextColumn::make('folio')
                    ->label('Folio'),
                TextColumn::make('reception_date')
                    ->label('Rescepción')
                    ->date(),
                TextColumn::make('response_deadline')
                    ->label('Respuesta')
                    ->date(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(DocumentStatus $state): string => $state->getColor()),
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
                ViewAction::make(),
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
        return  Action::make('derivar')
            ->label('Derivar')
            ->color('success')
            ->icon(Heroicon::RocketLaunch)
            ->requiresConfirmation()
            ->modalIcon(Heroicon::Envelope)
            ->modalDescription('Derivar el documento a otra oficina o usuario.')
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
                        fn(callable $get) =>
                        $get('destination_office_id')
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
                    ->label('Indicación')
                    ->rows(2)
                    ->maxLength(500)
                    ->placeholder('Instrucciones específicas para el destinatario'),
            ])
            ->action(function (Document $record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->movements()->create([
                        'document_id' => $record->id,
                        'origin_office_id' => $record->area_origen_id,
                        'origin_user_id' => Auth::id(),
                        'destination_office_id' => $data['destination_office_id'],
                        'destination_user_id' => $data['destination_user_id'],
                        'action' => MovementAction::DERIVACION,
                        'indication' => $data['indication'] ?? null,
                        'receipt_date' => now(),
                        'status' => MovementStatus::PENDING,
                    ]);
                    $record->update([
                        'status' => DocumentStatus::IN_PROCESS,
                        'id_office_destination' => $data['destination_office_id'],
                    ]);
                });
            })
            ->disabled(function (Document $record): bool {
                return $record->status !== DocumentStatus::REGISTERED || Auth::id() !== $record->user_id;
            });
    }
}
