<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class SeoSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 40;

    protected int $columns = 2;

    public function group(): string
    {
        return 'seo';
    }

    public function label(): string
    {
        return 'SEO';
    }

    public function icon(): string
    {
        return 'heroicon-o-magnifying-glass';
    }

    public function schema(): array
    {
        return [
            TextInput::make('meta_title')
                ->label('Meta título')
                ->maxLength(60)
                ->columnSpanFull(),

            Textarea::make('meta_description')
                ->label('Meta descripción')
                ->rows(3)
                ->maxLength(160)
                ->columnSpanFull(),

            TextInput::make('meta_keywords')
                ->label('Palabras clave')
                ->helperText('Separadas por coma')
                ->columnSpanFull(),

            FileUpload::make('og_image')
                ->label('Imagen Open Graph')
                ->image()
                ->disk('public')
                ->directory('settings')
                ->columnSpanFull(),

            TextInput::make('canonical_url')
                ->label('URL canónica')
                ->url(),

            Toggle::make('indexable')
                ->label('Permitir indexación (robots)')
                ->default(true),

            TextInput::make('google_analytics_id')
                ->label('Google Analytics ID'),

            TextInput::make('google_search_console')
                ->label('Google Search Console (verificación)'),
        ];
    }
}
