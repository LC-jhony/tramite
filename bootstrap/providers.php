<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\UserPanelProvider;
use App\Providers\SettingsServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    UserPanelProvider::class,
    SettingsServiceProvider::class,
];
