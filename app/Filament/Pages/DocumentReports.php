<?php

namespace App\Filament\Pages;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Pdf\Templates\DocumentListTemplate;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DocumentReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.document-reports';

    protected static ?string $title = 'Reportes de Documentos';

    protected static ?string $navigationLabel = 'Reportes';

    public ?array $data = [];

    public ?string $previewUrl = null;

    public int $totalFound = 0;

    public function mount(): void
    {
        $this->form->fill();
        $this->updateStats();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->columns(3)
            ->schema([
                Select::make('status')
                    ->label('Estado')
                    ->options(DocumentStatus::class)
                    ->placeholder('Todos'),
                Select::make('document_type_id')
                    ->label('Tipo de Documento')
                    ->options(DocumentType::where('status', true)->pluck('name', 'id'))
                    ->placeholder('Todos'),
                Select::make('current_office_id')
                    ->label('Oficina')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->placeholder('Todas'),
                Select::make('gestion_id')
                    ->label('Gestión')
                    ->options(Administration::where('status', true)->pluck('name', 'id'))
                    ->placeholder('Todas'),
                DatePicker::make('date_from')
                    ->label('Desde')
                    ->native(false),
                DatePicker::make('date_to')
                    ->label('Hasta')
                    ->native(false),
            ]);
    }

    protected function getQuery(): Builder
    {
        $formData = $this->form->getState();

        return Document::query()
            ->with(['type', 'currentOffice', 'customer'])
            ->when($formData['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($formData['document_type_id'], fn ($q, $v) => $q->where('document_type_id', $v))
            ->when($formData['current_office_id'], fn ($q, $v) => $q->where('current_office_id', $v))
            ->when($formData['gestion_id'], fn ($q, $v) => $q->where('gestion_id', $v))
            ->when($formData['date_from'], fn ($q, $v) => $q->whereDate('reception_date', '>=', $v))
            ->when($formData['date_to'], fn ($q, $v) => $q->whereDate('reception_date', '<=', $v))
            ->latest();
    }

    public function updateStats(): void
    {
        $this->totalFound = $this->getQuery()->count();
    }

    public function generatePreview(): void
    {
        $documents = $this->getQuery()->get();
        $formData = $this->form->getState();

        $filters = $this->getFilterLabels($formData);

        $this->previewUrl = DocumentListTemplate::make()
            ->data([
                'documents' => $documents,
                'filters' => $filters,
                'totalRecords' => $documents->count(),
            ])
            ->render()
            ->toBase64();

        $this->updateStats();
    }

    public function download(): mixed
    {
        $documents = $this->getQuery()->get();
        $formData = $this->form->getState();
        $filters = $this->getFilterLabels($formData);

        return DocumentListTemplate::make()
            ->data([
                'documents' => $documents,
                'filters' => $filters,
                'totalRecords' => $documents->count(),
            ])
            ->livewireDownload('reporte-documentos-'.now()->format('Ymd-His').'.pdf');
    }

    protected function getFilterLabels(array $formData): array
    {
        $labels = [];

        if ($formData['status']) {
            $labels['Estado'] = DocumentStatus::tryFrom($formData['status'])?->getLabel();
        }
        if ($formData['document_type_id']) {
            $labels['Tipo'] = DocumentType::find($formData['document_type_id'])?->name;
        }
        if ($formData['current_office_id']) {
            $labels['Oficina'] = Office::find($formData['current_office_id'])?->name;
        }
        if ($formData['gestion_id']) {
            $labels['Gestión'] = Administration::find($formData['gestion_id'])?->name;
        }
        if ($formData['date_from']) {
            $labels['Desde'] = $formData['date_from'];
        }
        if ($formData['date_to']) {
            $labels['Hasta'] = $formData['date_to'];
        }

        return $labels;
    }
}
