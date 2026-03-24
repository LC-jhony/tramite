<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\Movement;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public ?array $data = [];

    public ?Document $document = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Realizar consulta del tramite')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('document')->label('DNI / RUC'),
                        TextInput::make('case_number')->label('Número de caso'),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->document ? Movement::query()->where('document_id', $this->document->id)->with(['fromOffice', 'toOffice', 'user', 'document']) : Movement::query()->whereRaw('1 = 0'))
            ->paginated(false)
            ->columns([
                TextColumn::make('receipt_date')->label('Fecha'),
                TextColumn::make('action')->label('Acción'),
                TextColumn::make('fromOffice.name')->label('Desde'),
                TextColumn::make('toOffice.name')->label('Hasta'),
                TextColumn::make('user.name')->label('Usuario'),
                TextColumn::make('observation')->label('Observación'),
            ]);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $caseNumber = $data['case_number'] ?? null;
        $document = $data['document'] ?? null;

        if (! $caseNumber && ! $document) {
            $this->errorMessage = 'Ingrese los datos del trámite para consultar';
            $this->document = null;
            $this->dispatch('$refresh');

            return;
        }

        $this->errorMessage = null;

        $query = Document::query();

        if ($caseNumber) {
            $query->where('case_number', $caseNumber);
        } elseif ($document) {
            $customerIds = Customer::query()
                ->where('dni', $document)
                ->orWhere('ruc', $document)
                ->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        $this->document = $query->with(['documentFiles', 'type', 'currentOffice', 'customer'])->first();

        if (! $this->document) {
            $this->errorMessage = 'No se encontró ningún trámite con los datos ingresados';
        }

        $this->dispatch('$refresh');
    }
};
?>

<x-container>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" class="mt-4">
                Realizar consulta
            </x-filament::button>
        </div>
    </form>

    @if($errorMessage)
    <div class="relative overflow-hidden rounded-xl bg-red-500/10 border border-red-500/30 p-4 mt-4" role="alert">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-red-400 text-sm">{{ $errorMessage }}</p>
        </div>
    </div>
    @endif

    @if($document)
    <div class="mt-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl"></div>
            
            <h2 class="relative text-2xl font-light tracking-wide mb-6">Información del Trámite</h2>
            
            <div class="relative grid grid-cols-2 md:grid-cols-3 gap-6">
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Número de caso</span>
                    <p class="text-lg font-medium text-cyan-400">{{ $document->case_number }}</p>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Estado</span>
                    <p class="text-lg font-medium text-emerald-400">{{ $document->status }}</p>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Asunto</span>
                    <p class="text-base text-slate-200">{{ $document->subject }}</p>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Fecha de recepción</span>
                    <p class="text-base text-slate-300">{{ $document->reception_date }}</p>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Origen</span>
                    <p class="text-base text-slate-300">{{ $document->origen }}</p>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Tipo de documento</span>
                    <p class="text-base text-slate-300">{{ $document->type?->name }}</p>
                </div>
            </div>

            @if($document->documentFiles->isNotEmpty())
            <div class="relative mt-6 pt-4 border-t border-slate-700">
                <span class="text-xs text-slate-400 uppercase tracking-wider">Archivos adjuntos</span>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($document->documentFiles as $file)
                    <a href="{{ asset('storage/' . $file->path) }}" target="_blank" 
                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-700/50 hover:bg-slate-700 rounded-lg text-sm text-cyan-400 hover:text-cyan-300 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        {{ $file->original_name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h2 class="text-xl font-light tracking-wide mb-4 text-slate-700">Movimientos del Trámite</h2>
        {{ $this->table }}
    </div>
    @endif

    <x-filament-actions::modals />
</x-container>