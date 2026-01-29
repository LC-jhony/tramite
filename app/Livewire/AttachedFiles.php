<?php

namespace App\Livewire;

use App\Models\DocumentFile;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class AttachedFiles extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?int $documentId = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => DocumentFile::query()
                ->where('document_id', $this->documentId))
            ->paginated(false)
            ->columns([
                Panel::make([
                    Stack::make([
                        IconColumn::make('filename')
                            ->size('2xl')
                            ->alignCenter()
                            ->color(function (DocumentFile $record): string {
                                $extension = strtolower(pathinfo($record->filename, PATHINFO_EXTENSION));

                                return match ($extension) {
                                    'pdf' => 'danger',
                                    'doc', 'docx' => 'info',
                                    'xls', 'xlsx' => 'success',
                                    'txt' => 'gray',
                                    'jpg', 'jpeg', 'png', 'gif' => 'warning',
                                    default => 'secondary',
                                };
                            })
                            ->icon(function (DocumentFile $record): string {
                                $extension = strtolower(pathinfo($record->filename, PATHINFO_EXTENSION));

                                return match ($extension) {
                                    'pdf' => 'bi-file-pdf-fill',
                                    'doc' => 'bi-file-word-fill',
                                    'docx' => 'bi-file-word-fill',
                                    'xls' => 'bi-file-excel-fill',
                                    'xlsx' => 'bi-file-excel-fill',
                                    'txt' => 'bi-file-text-fill',
                                    'jpg' => 'bi-file-image-fill',
                                    'jpeg' => 'bi-file-image-fill',
                                    'png' => 'bi-file-image-fill',
                                    'gif' => 'bi-file-image-fill',
                                    default => 'bi-file-fill',
                                };
                            }),
                        TextColumn::make('document.case_number')->alignCenter(),
                        // TextColumn::make('mime_type')->alignCenter(),
                        // TextColumn::make('size')->alignCenter(),
                        TextColumn::make('created_at')
                            ->date()->alignCenter(),
                    ]),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 2,
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Visualizar')
                    ->icon(Heroicon::Eye)
                    ->color('primary')
                    ->button()
                    ->action(function (DocumentFile $record) {
                        if (! \Illuminate\Support\Facades\Storage::exists($record->path)) {
                            $this->js('alert("Archivo no encontrado.");');

                            return;
                        }

                        return \Illuminate\Support\Facades\Storage::response($record->path, $record->filename, [
                            'Content-Disposition' => 'inline',
                        ]);
                    }),
                Action::make('download')
                    ->label('Descargar')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('success')
                    ->button()
                    ->url(function (DocumentFile $record): string {
                        if (! Storage::exists($record->path)) {
                            return '#';
                        }

                        return Storage::url($record->path);
                    })
                    ->openUrlInNewTab(),
            ], position: RecordActionsPosition::AfterContent);
    }

    public function render(): View
    {
        return view('livewire.attached-files');
    }
}
