<?php

namespace App\Filament\User\Pages;

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Models\Document;
use App\Models\Priority;
use App\Trait\HasForwardAction;
use App\Trait\HasReceiveAction;
use App\Trait\HasRejectAction;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Auth;

class DocumentReception extends Page implements HasTable
{
    use HasForwardAction, HasReceiveAction, HasRejectAction, HasTabs, InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $title = 'Recepción de Documentos';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                RenderHook::make('filament-panels::page.before'),
                EmbeddedTable::make(),
                RenderHook::make('filament-panels::page.after'),
            ]);
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

    public function table(Table $table): Table
    {
        $officeId = Auth::user()?->office_id;

        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->query(
                Document::query()
                    ->with([
                        'latestMovement',
                        'latestMovement.toOffice',
                        'latestMovement.fromOffice',
                        'documentFiles',
                    ])
                    ->whereHas('movements', function ($query) use ($officeId) {
                        $query->where('to_office_id', $officeId)
                            ->where('action', 'derivado');
                    })
            )
            ->columns([
                TextColumn::make('document_number')
                    ->label('Nro. Trámite')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currentOffice.name')
                    ->label('Remitente')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        default => 'info',
                    }),
                TextColumn::make('type.name')
                    ->label('Tipo'),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50),
                TextColumn::make('latestMovement.toOffice.name')
                    ->label('Derivado a')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        default => 'info',
                    }),
                TextColumn::make('reception_date')
                    ->label('Fecha Registro')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                self::getReceiveAction($this),
                self::getForwardAction()
                    ->label('Responder'),
                self::getRejectAction(),
                MediaAction::make('pdf')
                    ->label('documentos')
                    ->icon('bi-file-pdf')
                    ->color('danger')
                    ->media(fn ($record) => $record->documentFiles->first()?->path
                        ? asset('storage/'.$record->documentFiles->first()->path)
                        : null),
            ]);
    }

    public static function customerForm(Form $form): Form
    {
        return $form->schema(CustomerForm::getComponents());
    }
}
