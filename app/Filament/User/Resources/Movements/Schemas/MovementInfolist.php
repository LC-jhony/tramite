<?php

namespace App\Filament\User\Resources\Movements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('document.id')
                    ->label('Document'),
                TextEntry::make('originOffice.name')
                    ->label('Origin office'),
                TextEntry::make('originUser.name')
                    ->label('Origin user'),
                TextEntry::make('destinationOffice.name')
                    ->label('Destination office'),
                TextEntry::make('destinationUser.name')
                    ->label('Destination user'),
                TextEntry::make('action'),
                TextEntry::make('indication')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('observation')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('receipt_date')
                    ->date(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
