<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;

beforeEach(function () {
    $this->withoutVite();

    $this->html = view('layouts.app', [
        'slot' => new HtmlString('<main>Contenido</main>'),
    ])->render();
});

test('la navegación pública usa el enlace nativo de Filament', function () {
    expect($this->html)
        ->toContain('fi-link')
        ->toContain('href="'.route('register.document').'"')
        ->toContain('Registrar Tramite')
        ->toContain('bg-primary-600');
});

test('marca el enlace activo con aria-current y peso semibold', function () {
    request()->setRouteResolver(
        fn ($request = null) => Route::getRoutes()->getByName('register.document'),
    );

    $html = view('layouts.app', [
        'slot' => new HtmlString('<main>Contenido</main>'),
    ])->render();

    expect($html)
        ->toContain('aria-current="page"')
        ->toContain('fi-font-semibold');
});

test('aplica el tema guardado antes de pintar la página', function () {
    expect($this->html)
        ->toContain('localStorage.getItem(themeStorageKey)')
        ->toContain("classList.toggle('dark'")
        ->toContain('localStorage.setItem(themeStorageKey, nextTheme)');
});

test('ofrece un botón para alternar entre modo claro y oscuro', function () {
    expect($this->html)
        ->toContain('x-on:click="toggleTheme()"')
        ->toContain('aria-label="Cambiar entre modo claro y oscuro"')
        ->toContain('fi-icon fi-size-md hidden dark:block')
        ->toContain('fi-icon fi-size-md dark:hidden');
});
