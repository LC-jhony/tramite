<?php

use App\Livewire\DocumentRegister;
use Illuminate\Support\Facades\Route;

Route::livewire('/', DocumentRegister::class)->name('create.tramite');
Route::livewire('/consult-procedure', 'pages::⚡-consult-procedure')->name('consulta.document');
