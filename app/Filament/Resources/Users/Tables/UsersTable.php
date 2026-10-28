<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Usuarios')
            ->description('Gestiona los usuarios del sistema.')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->copyable(),
                // TextColumn::make('roles.name')
                //     ->badge(),
                // TextColumn::make('email_verified_at')
                //     ->label('Verificado')
                //     ->dateTime()
                //     ->sortable()
                //     ->badge()
                //     ->color(fn($state): string => $state ? 'success' : 'warning')
                //     ->formatStateUsing(fn($state): string => $state ? 'Verificado' : 'Pendiente'),
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
                TextColumn::make('office.name')
                    ->label('Oficina')
                    ->searchable()
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay usuarios')
            ->emptyStateDescription('Crea el primer usuario para comenzar.')
            ->emptyStateIcon(Heroicon::Users)
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc');
    }
}
