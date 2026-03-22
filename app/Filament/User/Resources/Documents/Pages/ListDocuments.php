<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use App\Models\Priority;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('All')];
        $officeId = Auth::user()?->office_id;
        $priorities = Priority::where('status', true)
            ->withCount([
                'documents' => function ($query) use ($officeId) {
                    $query->whereHas('movements', function ($q) use ($officeId) {
                        $q->where('to_office_id', $officeId)
                            ->where('action', 'derivado');
                    });
                },
            ])
            ->orderBy('id', 'asc')
            ->get();

        foreach ($priorities as $priority) {
            $name = $priority->name;
            $slug = str($name)->slug()->toString();

            $tabs[$slug] = Tab::make($name)
                ->badge($priority->documents_count)
                ->modifyQueryUsing(function ($query) use ($officeId, $priority) {
                    return $query->where('priority_id', $priority->id)
                        ->whereHas('movements', function ($q) use ($officeId) {
                            $q->where('to_office_id', $officeId)
                                ->where('action', 'derivado');
                        });
                });
        }

        return $tabs;
    }
}
