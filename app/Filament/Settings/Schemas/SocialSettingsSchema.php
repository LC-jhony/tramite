<?php

namespace App\Filament\Settings\Schemas;

use Filament\Forms\Components\TextInput;

class SocialSettingsSchema extends BaseSettingsSchema
{
    protected int $sort = 20;

    protected int $columns = 2;

    public function group(): string
    {
        return 'social';
    }

    public function label(): string
    {
        return 'Redes Sociales';
    }

    public function icon(): string
    {
        return 'heroicon-o-share';
    }

    public function schema(): array
    {
        return [
            TextInput::make('facebook')
                ->label('Facebook')
                ->prefixIcon('heroicon-o-globe-alt')
                ->url()
                ->maxLength(255),

            TextInput::make('instagram')
                ->label('Instagram')
                ->url()
                ->maxLength(255),

            TextInput::make('x')
                ->label('X (Twitter)')
                ->url()
                ->maxLength(255),

            TextInput::make('tiktok')
                ->label('TikTok')
                ->url()
                ->maxLength(255),

            TextInput::make('youtube')
                ->label('YouTube')
                ->url()
                ->maxLength(255),

            TextInput::make('linkedin')
                ->label('LinkedIn')
                ->url()
                ->maxLength(255),

            TextInput::make('whatsapp')
                ->label('WhatsApp')
                ->tel()
                ->helperText('Incluye código de país, ej: +51999999999'),
        ];
    }
}
