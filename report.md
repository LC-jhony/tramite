# Módulo de Reportes - Plan de Implementación

## Overview

Implementación de módulo de generación de reportes en PDF para el sistema Tramita YA, utilizando el package **PDF Studio** (`sarder/pdfstudio`).

---

## Package a Utilizar

**PDF Studio** - Wrapper moderno de DomPDF/Chromium para Laravel
- Repository: https://github.com/sarderiftekhar/pdf-studio
- Documentación: https://sarderiftekhar.github.io/pdf-studio/

### Características Principales
- Múltiples drivers: Chromium (recomendado), dompdf, wkhtmltopdf, Gotenberg, WeasyPrint
- Soporte completo para Tailwind CSS v4 (con Chromium)
- API fluente y orientada a objetos
- Caché de renderizado
- Marcas de agua y protección con contraseña
- Generación de thumbnails
- Fusión y división de PDFs
- Integración con Livewire/Filament

---

## Instalación

```bash
# Instalar el package
composer require sarder/pdfstudio

# Publicar configuración
php artisan vendor:publish --tag=pdf-studio-config
# → config/pdf-studio.php

# Instalar dependencias opcionales (interactivo)
php artisan pdf-studio:install

# O instalar todo de una vez
php artisan pdf-studio:install --all
```

### Drivers Disponibles

```bash
# Chromium (RECOMENDADO - soporta Tailwind completo)
composer require spatie/browsershot

# dompdf (no requiere binarios externos)
composer require dompdf/dompdf

# Para manipulación de PDFs (unir, marcar, dividir)
composer require setasign/fpdi
```

### Configuración del Driver

En `.env`:
```env
PDF_STUDIO_DRIVER=chromium
```

---

## Configuración (`config/pdf-studio.php`)

```php
return [
    // Driver: 'chromium', 'dompdf', 'wkhtmltopdf', 'gotenberg', 'weasyprint', 'cloudflare', 'fake'
    'default_driver' => env('PDF_STUDIO_DRIVER', 'chromium'),

    // Tailwind CSS compilation
    'tailwind' => [
        'binary' => env('TAILWIND_BINARY', base_path('node_modules/.bin/tailwindcss')),
        'config' => base_path('tailwind.config.js'),
        'cache' => [
            'enabled' => true,
            'path'    => storage_path('framework/cache/pdf-studio/tailwind'),
        ],
    ],

    // CSS Framework: 'tailwind', 'bootstrap', o 'none'
    'css_framework' => 'tailwind',

    // Caché de renderizado
    'render_cache' => [
        'enabled' => true,
        'store'   => null,  // usa el cache por defecto
        'ttl'     => 3600,  // 1 hora
    ],

    // Logging
    'logging' => [
        'enabled' => env('PDF_STUDIO_LOGGING', false),
        'channel' => null,
    ],
];
```

---

## Estructura de Archivos

```
app/
├── Http/
│   └── Controllers/
│       └── ReportController.php          # Controlador principal de reportes
├── Filament/
│   └── Pages/
│       └── Reports.php                   # Página Filament para UI de reportes
├── Pdf/
│   └── Templates/
│       ├── DocumentsTemplate.php         # Template de documentos
│       └── MovementsTemplate.php         # Template de movimientos
└── Traits/
    └── HasReportGeneration.php           # Trait compartido para generación PDF

resources/
├── views/
│   └── pdf/
│       ├── layout.blade.php              # Layout base con header/footer
│       ├── documents.blade.php           # Reporte de documentos
│       ├── movements.blade.php           # Reporte de movimientos/derivaciones
│       ├── receptions.blade.php          # Reporte de recepciones
│       └── _partials/
│           ├── header.blade.php          # Header común (logo, título)
│           └── footer.blade.php          # Footer común (fecha, página)

routes/
└── web.php (o admin.php)                 # Rutas para reportes

public/
└── images/
    └── logo-report.png                   # Logo para reportes
```

---

## Implementación Detallada

### 1. Controlador de Reportes

**Archivo**: `app/Http/Controllers/ReportController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Movement;
use App\Models\Office;
use App\Enum\DocumentStatus;
use Illuminate\Http\Request;
use PdfStudio\Laravel\Facades\Pdf;

class ReportController extends Controller
{
    /**
     * Reporte de documentos con filtros
     */
    public function documents(Request $request)
    {
        $query = Document::with([
            'type',
            'customer',
            'currentOffice',
            'administration',
            'user',
            'priority'
        ]);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        if ($request->filled('office_id')) {
            $query->where('current_office_id', $request->office_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('reception_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('reception_date', '<=', $request->date_to);
        }

        if ($request->filled('gestion_id')) {
            $query->where('gestion_id', $request->gestion_id);
        }

        $documents = $query->latest()->get();

        $filters = collect([
            'Estado' => $request->status ? DocumentStatus::from($request->status)->getLabel() : null,
            'Fecha Desde' => $request->date_from,
            'Fecha Hasta' => $request->date_to,
        ])->filter()->toArray();

        return Pdf::view('pdf.documents')
            ->data([
                'documents' => $documents,
                'filters' => $filters,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'totalRecords' => $documents->count(),
            ])
            ->format('A4')
            ->landscape()
            ->margins(top: 15, right: 10, bottom: 20, left: 10)
            ->download('reporte-documentos-' . now()->format('Y-m-d-His') . '.pdf');
    }

    /**
     * Reporte de movimientos/derivaciones
     */
    public function movements(Request $request)
    {
        $query = Movement::with([
            'document',
            'originOffice',
            'destinationOffice',
            'user'
        ]);

        if ($request->filled('document_id')) {
            $query->where('document_id', $request->document_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->get();

        return Pdf::view('pdf.movements')
            ->data([
                'movements' => $movements,
                'filters' => $request->only(['action', 'date_from', 'date_to']),
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'totalRecords' => $movements->count(),
            ])
            ->format('A4')
            ->landscape()
            ->download('reporte-movimientos-' . now()->format('Y-m-d-His') . '.pdf');
    }

    /**
     * Reporte de documentos por oficina
     */
    public function byOffice(Office $office)
    {
        $documents = Document::with(['type', 'customer', 'user'])
            ->where('current_office_id', $office->id)
            ->latest()
            ->get();

        return Pdf::view('pdf.documents')
            ->data([
                'documents' => $documents,
                'office' => $office,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'totalRecords' => $documents->count(),
            ])
            ->format('A4')
            ->download('reporte-oficina-' . $office->id . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Vista previa del reporte (para Filament)
     */
    public function preview(Request $request)
    {
        $data = $request->only(['status', 'date_from', 'date_to', 'office_id']);
        
        return Pdf::view('pdf.documents')
            ->data($data)
            ->format('A4')
            ->landscape()
            ->inline('preview.pdf');
    }
}
```

---

### 2. Página Filament para UI de Reportes

**Archivo**: `app/Filament/Pages/Reports.php`

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Schemas\Reports\DocumentsReportSchema;
use App\Models\Office;
use App\Models\DocumentType;
use App\Models\Administration;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = Heroicon::DocumentChartBar;
    protected static string $view = 'filament.pages.reports';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(DocumentsReportSchema::make())
            ->statePath('data');
    }

    public function generateDocumentsReport(): void
    {
        $data = $this->form->getState();

        // Validar fechas
        if (!empty($data['date_from']) && !empty($data['date_to'])) {
            if ($data['date_from'] > $data['date_to']) {
                Notification::make()
                    ->title('Fecha inválida')
                    ->body('La fecha desde no puede ser mayor a la fecha hasta')
                    ->danger()
                    ->send();

                return;
            }
        }

        // Construir URL con parámetros
        $queryParams = http_build_query(array_filter($data));

        // Redirigir a la ruta del reporte
        redirect()->to(route('reports.documents') . '?' . $queryParams);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_reports');
    }
}
```

---

### 3. Schema del Formulario de Reportes

**Archivo**: `app/Filament/Schemas/Reports/DocumentsReportSchema.php`

```php
<?php

namespace App\Filament\Schemas\Reports;

use App\Enum\DocumentStatus;
use App\Models\Office;
use App\Models\DocumentType;
use App\Models\Administration;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DocumentsReportSchema
{
    public static function make(): array
    {
        return [
            Section::make('Filtros del Reporte')
                ->description('Seleccione los filtros para generar el reporte de documentos')
                ->icon(Heroicon::Funnel)
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('status')
                                ->label('Estado')
                                ->options(DocumentStatus::class)
                                ->native(false)
                                ->placeholder('Todos los estados'),

                            Select::make('document_type_id')
                                ->label('Tipo de Documento')
                                ->options(
                                    DocumentType::query()->active()->pluck('name', 'id')
                                )
                                ->native(false)
                                ->placeholder('Todos los tipos'),

                            Select::make('office_id')
                                ->label('Oficina')
                                ->options(
                                    Office::query()->active()->pluck('name', 'id')
                                )
                                ->native(false)
                                ->placeholder('Todas las oficinas'),

                            Select::make('gestion_id')
                                ->label('Gestión/Año')
                                ->options(
                                    Administration::query()->pluck('name', 'id')
                                )
                                ->native(false)
                                ->placeholder('Todas las gestiones'),

                            DatePicker::make('date_from')
                                ->label('Fecha Desde')
                                ->maxDate(now())
                                ->native(false),

                            DatePicker::make('date_to')
                                ->label('Fecha Hasta')
                                ->maxDate(now())
                                ->native(false),
                        ]),
                ])
                ->footerActions([
                    Action::make('generate')
                        ->label('Generar Reporte PDF')
                        ->icon(Heroicon::DocumentArrowDown)
                        ->action('generateDocumentsReport')
                        ->color('primary')
                        ->submit(),

                    Action::make('clear')
                        ->label('Limpiar Filtros')
                        ->icon(Heroicon::Eraser)
                        ->action(fn($form) => $form->fill())
                        ->color('gray'),
                ])
                ->footerActionsAlignment('center'),
        ];
    }
}
```

---

### 4. Vista Filament para la Página de Reportes

**Archivo**: `resources/views/filament/pages/reports.blade.php`

```blade
<x-filament-panels::page>
    <div class="fi-ta-content relative">
        {{ $this->form }}
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Tarjetas de resumen --}}
        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Documentos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ \App\Models\Document::count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">En Proceso</p>
                    <p class="text-2xl font-semibold text-info-600 dark:text-info-400">
                        {{ \App\Models\Document::where('status', 'en_proceso')->count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Finalizados</p>
                    <p class="text-2xl font-semibold text-success-600 dark:text-success-400">
                        {{ \App\Models\Document::where('status', 'finalizado')->count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Registrados</p>
                    <p class="text-2xl font-semibold text-gray-600 dark:text-gray-400">
                        {{ \App\Models\Document::where('status', 'registrado')->count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Respondidos</p>
                    <p class="text-2xl font-semibold text-primary-600 dark:text-primary-400">
                        {{ \App\Models\Document::where('status', 'respondido')->count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center gap-x-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rechazados</p>
                    <p class="text-2xl font-semibold text-danger-600 dark:text-danger-400">
                        {{ \App\Models\Document::where('status', 'rechazado')->count() }}
                    </p>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
```

---

### 5. Templates Blade para PDF

#### Layout Base

**Archivo**: `resources/views/pdf/layout.blade.php`

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reporte')</title>
    <style>
        @page {
            margin: 15mm 10mm 20mm 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header img {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 16px;
            color: #1e40af;
            margin: 3px 0;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 10px;
            color: #6b7280;
        }

        .report-info {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }

        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-info td {
            padding: 4px 8px;
        }

        .report-info td:first-child {
            font-weight: 600;
            width: 140px;
            color: #374151;
        }

        .report-info td:last-child {
            color: #111827;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .main-table th {
            background-color: #1e40af;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .main-table td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            vertical-align: top;
        }

        .main-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-registrado { background-color: #9ca3af; color: white; }
        .status-en_proceso { background-color: #3b82f6; color: white; }
        .status-respondido { background-color: #8b5cf6; color: white; }
        .status-finalizado { background-color: #10b981; color: white; }
        .status-rechazado { background-color: #ef4444; color: white; }
        .status-cancelado { background-color: #f59e0b; color: white; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-gray { color: #6b7280; }
        
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo-report.png') }}" alt="Logo">
        <h1>{{ config('app.name') }}</h1>
        <p class="subtitle">Sistema de Gestión Documental</p>
    </div>

    @yield('content')

    <div class="footer">
        <p>Generado el: {{ date('d/m/Y H:i:s') }} | Página {PAGENO} de {nbpg}</p>
    </div>
</body>
</html>
```

---

#### Reporte de Documentos

**Archivo**: `resources/views/pdf/documents.blade.php`

```blade
@extends('pdf.layout')

@section('title', 'Reporte de Documentos')

@section('content')
    <div class="report-info no-break">
        <table>
            @if(!empty($filters['Estado']))
            <tr>
                <td>Estado:</td>
                <td>{{ $filters['Estado'] }}</td>
            </tr>
            @endif

            @if(!empty($filters['Fecha Desde']))
            <tr>
                <td>Fecha Desde:</td>
                <td>{{ $filters['Fecha Desde'] }}</td>
            </tr>
            @endif

            @if(!empty($filters['Fecha Hasta']))
            <tr>
                <td>Fecha Hasta:</td>
                <td>{{ $filters['Fecha Hasta'] }}</td>
            </tr>
            @endif

            <tr>
                <td>Total Registros:</td>
                <td class="font-bold">{{ $totalRecords }}</td>
            </tr>

            <tr>
                <td>Generado:</td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">N°</th>
                <th style="width: 12%;">Número</th>
                <th style="width: 12%;">Expediente</th>
                <th style="width: 25%;">Asunto</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 18%;">Oficina</th>
                <th style="width: 13%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $index => $document)
            <tr class="no-break">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $document->document_number }}</td>
                <td>{{ $document->case_number }}</td>
                <td>{{ Str::limit($document->subject, 45) }}</td>
                <td>{{ $document->type->name ?? 'N/A' }}</td>
                <td>{{ $document->currentOffice->name ?? 'N/A' }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $document->status }}">
                        {{ $document->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray">No se encontraron documentos</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
```

---

#### Reporte de Movimientos

**Archivo**: `resources/views/pdf/movements.blade.php`

```blade
@extends('pdf.layout')

@section('title', 'Reporte de Movimientos')

@section('content')
    <div class="report-info no-break">
        <table>
            @if(!empty($filters['action']))
            <tr>
                <td>Acción:</td>
                <td>{{ ucfirst($filters['action']) }}</td>
            </tr>
            @endif

            @if(!empty($filters['date_from']))
            <tr>
                <td>Fecha Desde:</td>
                <td>{{ $filters['date_from'] }}</td>
            </tr>
            @endif

            @if(!empty($filters['date_to']))
            <tr>
                <td>Fecha Hasta:</td>
                <td>{{ $filters['date_to'] }}</td>
            </tr>
            @endif

            <tr>
                <td>Total Movimientos:</td>
                <td class="font-bold">{{ $totalRecords }}</td>
            </tr>

            <tr>
                <td>Generado:</td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">N°</th>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 15%;">Documento</th>
                <th style="width: 20%;">Origen</th>
                <th style="width: 20%;">Destino</th>
                <th style="width: 15%;" class="text-center">Acción</th>
                <th style="width: 10%;">Usuario</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $index => $movement)
            <tr class="no-break">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $movement->document->document_number ?? 'N/A' }}</td>
                <td>{{ $movement->originOffice->name ?? 'N/A' }}</td>
                <td>{{ $movement->destinationOffice->name ?? 'N/A' }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $movement->action === 'derivado' ? 'en_proceso' : 'registrado' }}">
                        {{ $movement->action }}
                    </span>
                </td>
                <td>{{ $movement->user->name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray">No se encontraron movimientos</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
```

---

### 6. Rutas

**Archivo**: `routes/web.php` o `routes/admin.php`

```php
<?php

use App\Http\Controllers\ReportController;
use App\Filament\Pages\Reports;

// Registrar página Filament
Reports::registerRoute();

// Rutas para generación de PDFs
Route::middleware(['auth', 'sanctum'])->group(function () {
    Route::get('/admin/reports/documents', [ReportController::class, 'documents'])
        ->name('reports.documents');

    Route::get('/admin/reports/movements', [ReportController::class, 'movements'])
        ->name('reports.movements');

    Route::get('/admin/reports/office/{office}', [ReportController::class, 'byOffice'])
        ->name('reports.office');

    // Vista previa
    Route::get('/admin/reports/preview', [ReportController::class, 'preview'])
        ->name('reports.preview');
});
```

---

### 7. Trait para Generación de Reportes (Opcional)

**Archivo**: `app/Traits/HasReportGeneration.php`

```php
<?php

namespace App\Traits;

use PdfStudio\Laravel\Facades\Pdf;
use PdfStudio\Laravel\Result\PdfResult;

trait HasReportGeneration
{
    /**
     * Generar PDF desde una vista
     */
    protected function generatePdf(
        string $view,
        array $data = [],
        ?string $filename = null,
        string $format = 'A4',
        bool $landscape = false
    ): PdfResult {
        $pdf = Pdf::view($view)
            ->data($data)
            ->format($format);

        if ($landscape) {
            $pdf->landscape();
        }

        return $pdf->render();
    }

    /**
     * Descargar PDF
     */
    protected function downloadPdf(
        string $view,
        array $data = [],
        string $filename,
        string $format = 'A4',
        bool $landscape = false
    ) {
        return Pdf::view($view)
            ->data($data)
            ->format($format)
            ->landscape($landscape)
            ->download($filename);
    }

    /**
     * Guardar PDF en storage
     */
    protected function savePdf(
        string $view,
        array $data = [],
        string $path = 'reports',
        string $disk = 'public'
    ): string {
        $filename = now()->format('Y-m-d-His') . '.pdf';
        
        Pdf::view($view)
            ->data($data)
            ->format('A4')
            ->save($path . '/' . $filename, $disk);

        return $path . '/' . $filename;
    }

    /**
     * Generar PDF con caché
     */
    protected function generateCachedPdf(
        string $view,
        array $data = [],
        int $ttl = 3600
    ): PdfResult {
        return Pdf::view($view)
            ->data($data)
            ->cache($ttl)
            ->render();
    }

    /**
     * Generar thumbnail de PDF
     */
    protected function generatePdfThumbnail(
        string $view,
        array $data = [],
        int $width = 300,
        string $format = 'png'
    ) {
        return Pdf::view($view)
            ->data($data)
            ->thumbnail(
                width: $width,
                format: $format,
                quality: 85,
                page: 1
            );
    }
}
```

---

## Reportes a Implementar

| Reporte | Descripción | Filtros Disponibles | Método |
|---------|-------------|---------------------|--------|
| **Documentos** | Listado de documentos registrados | Estado, Tipo, Oficina, Fecha, Gestión | `documents()` |
| **Movimientos** | Historial de derivaciones | Documento, Acción, Fecha | `movements()` |
| **Por Oficina** | Documentos en una oficina específica | Oficina, Estado, Fecha | `byOffice()` |
| **Por Usuario** | Documentos asignados a un usuario | Usuario, Estado, Fecha | `byUser()` |
| **Estadístico** | Resumen por período | Gestión, Mes, Tipo | `statistics()` |

---

## Métodos PDF Studio Disponibles

### Métodos Fluentes Principales

```php
Pdf::view('pdf.documents')           // Cargar vista Blade
    ->data(['documents' => $docs])   // Pasar datos
    ->driver('chromium')             // Cambiar driver
    ->format('A4')                   // Formato: A4, A3, A5, Letter, Legal
    ->landscape()                    // o ->portrait()
    ->margins(top: 15, right: 10)    // Márgenes en mm
    ->cache(3600)                    // Caché por 1 hora
    ->download('report.pdf');        // Descargar
```

### Opciones de Salida

```php
// Descargar
return Pdf::view('pdf.doc')->download('file.pdf');

// Mostrar en navegador (inline)
return Pdf::view('pdf.doc')->inline('file.pdf');

// Guardar en Storage
Pdf::view('pdf.doc')->save('path/file.pdf', 's3');

// Obtener resultado raw
$result = Pdf::view('pdf.doc')->render();
echo $result->bytes;         // Tamaño en bytes
echo $result->renderTimeMs;  // Tiempo de renderizado
```

### Para Livewire/Filament

```php
// En componente Livewire
public function downloadPdf(): mixed
{
    return Pdf::view('pdf.invoice')
        ->data(['invoice' => $this->invoice])
        ->livewireDownload('invoice.pdf');
}

// Base64 para modales Filament
$base64 = Pdf::view('pdf.doc')
    ->data($data)
    ->render()
    ->toBase64();
```

### Características Avanzadas

```php
// Marca de agua
Pdf::view('pdf.doc')
    ->watermark('BORRADOR', opacity: 0.3, fontSize: 72)
    ->download('doc.pdf');

// Marca de agua con imagen
Pdf::view('pdf.doc')
    ->watermarkImage(storage_path('images/logo.png'), opacity: 0.2)
    ->download('doc.pdf');

// Protección con contraseña
Pdf::view('pdf.confidential')
    ->protect(userPassword: 'user123', ownerPassword: 'admin456')
    ->download('protected.pdf');

// Unir múltiples PDFs
$result = Pdf::merge([
    storage_path('pdf/cover.pdf'),
    storage_path('pdf/report.pdf'),
]);

// Dividir PDF
$parts = Pdf::split($pdfContent, ['1-3', '4-6', '7-10']);

// Generar thumbnail
$thumb = Pdf::view('pdf.doc')
    ->thumbnail(width: 300, format: 'png', quality: 85);
```

---

## Permisos Requeridos (FilamentShield)

```php
// En DatabaseSeeder o PermissionSeeder
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'view_reports']);
Permission::create(['name' => 'generate_reports']);
Permission::create(['name' => 'download_reports']);

// Asignar a roles
$adminRole = Role::findByName('admin');
$adminRole->givePermissionTo(['view_reports', 'generate_reports', 'download_reports']);
```

---

## Consideraciones

### Limitaciones de DomPDF
- **CSS**: Soporte limitado (no usa CSS moderno como flexbox, grid)
- **Fuentes**: Usar fuentes compatibles (DejaVu Sans para caracteres especiales)
- **Imágenes**: Usar rutas absolutas con `public_path()` o base64
- **JavaScript**: No hay soporte para JS

### Recomendación: Usar Chromium

Para mejor soporte de Tailwind CSS:

```bash
composer require spatie/browsershot
```

```env
# .env
PDF_STUDIO_DRIVER=chromium
```

### Mejoras Futuras
1. **Queue para reportes grandes**: Usar `RenderPdfJob` para reportes con muchos registros
2. **Export a Excel**: Agregar `maatwebsite/excel` para exportación alternativa
3. **Gráficos**: Generar imágenes de gráficos y embeber en PDF
4. **Email de reportes**: Enviar reportes programados por email
5. **Historial de reportes**: Guardar reportes generados para descarga posterior
6. **Plantillas registradas**: Usar el sistema de templates de PDF Studio

---

## Testing

```bash
# Crear test para el controlador
php artisan make:test --pest ReportControllerTest

# Crear test para la página Filament
php artisan make:test --pest ReportsPageTest
```

### Ejemplo de Test

```php
// tests/Feature/ReportControllerTest.php
it('generates documents report pdf', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get(route('reports.documents', [
            'status' => 'en_proceso',
            'date_from' => now()->startOfMonth(),
            'date_to' => now(),
        ]));

    $response->assertDownload('reporte-documentos');
});

it('generates movements report pdf', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get(route('reports.movements'));

    $response->assertDownload('reporte-movimientos');
});
```

---

## Checklist de Implementación

- [ ] Instalar package `sarder/pdfstudio`
- [ ] Instalar driver Chromium: `composer require spatie/browsershot`
- [ ] Publicar configuración: `php artisan vendor:publish --tag=pdf-studio-config`
- [ ] Configurar `PDF_STUDIO_DRIVER=chromium` en `.env`
- [ ] Crear `ReportController.php`
- [ ] Crear página Filament `Reports.php`
- [ ] Crear schema `DocumentsReportSchema.php`
- [ ] Crear vista `filament/pages/reports.blade.php`
- [ ] Crear layout PDF `pdf/layout.blade.php`
- [ ] Crear template `pdf/documents.blade.php`
- [ ] Crear template `pdf/movements.blade.php`
- [ ] Configurar rutas
- [ ] Agregar logo para reportes en `public/images/`
- [ ] Crear permisos en FilamentShield
- [ ] Probar generación de reportes
- [ ] Probar con grandes volúmenes de datos
- [ ] Documentar uso en README

---

## Referencias

- PDF Studio Docs: https://sarderiftekhar.github.io/pdf-studio/
- GitHub: https://github.com/sarderiftekhar/pdf-studio
- Drivers: https://sarderiftekhar.github.io/pdf-studio/user-guide.html#drivers
- Fluent API: https://sarderiftekhar.github.io/pdf-studio/user-guide.html#fluent-api
- Filament Pages: https://filamentphp.com/docs/5.x/panels/pages
- Filament Forms: https://filamentphp.com/docs/5.x/forms/overview
