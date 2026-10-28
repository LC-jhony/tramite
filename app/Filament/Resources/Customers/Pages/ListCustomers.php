<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }

    /**
     * El campo `representation` es booleano: `false` es persona natural y `true` es persona jurídica.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make()
                ->icon(Heroicon::RectangleStack)
                ->badge(fn (): string => (string) Customer::query()->count()),

            'Persona Natural' => Tab::make()
                ->icon(Heroicon::User)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('representation', false))
                ->badge(fn (): string => (string) Customer::where('representation', false)->count()),

            'Persona Jurídica' => Tab::make()
                ->icon(Heroicon::BuildingOffice)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('representation', true))
                ->badge(fn (): string => (string) Customer::where('representation', true)->count()),
        ];
    }
}
