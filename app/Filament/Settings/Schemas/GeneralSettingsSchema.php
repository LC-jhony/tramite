<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class GeneralSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 10;

    protected int $columns = 2;

    public function group(): string
    {
        return 'general';
    }

    public function label(): string
    {
        return 'General';
    }

    public function icon(): string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public function schema(): array
    {
        return [
            TextInput::make('site_name')
                ->label('Nombre del sitio')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true),

            TextInput::make('site_url')
                ->label('URL del sitio')
                ->url()
                ->maxLength(255),

            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('settings')
                ->live(),

            FileUpload::make('logo_dark')
                ->label('Logo (modo oscuro)')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('settings')
                ->live(),

            FileUpload::make('favicon')
                ->label('Favicon')
                ->image()
                ->disk('public')
                ->directory('settings')
                ->live(),

            Textarea::make('description')
                ->label('Descripción del sitio')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull()
                ->live(onBlur: true),

            TextInput::make('support_email')
                ->label('Correo de soporte')
                ->email()
                ->maxLength(255)
                ->live(onBlur: true),

            TextInput::make('support_phone')
                ->label('Teléfono de soporte')
                ->tel()
                ->live(onBlur: true),
        ];
    }
}
