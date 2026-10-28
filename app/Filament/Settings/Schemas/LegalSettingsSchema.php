<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;

class LegalSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 30;

    protected int $columns = 1;

    public function group(): string
    {
        return 'legal';
    }

    public function label(): string
    {
        return 'Términos y Condiciones';
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function schema(): array
    {
        return [
            Toggle::make('require_acceptance')
                ->label('Exigir aceptación explícita al registrarse')
                ->default(true),

            DatePicker::make('last_updated_at')
                ->label('Última actualización')
                ->native(false),

            RichEditor::make('terms')
                ->label('Términos y condiciones')
                ->columnSpanFull(),

            RichEditor::make('privacy_policy')
                ->label('Política de privacidad')
                ->columnSpanFull(),

            RichEditor::make('cookies_policy')
                ->label('Política de cookies')
                ->columnSpanFull(),
        ];
    }
}
