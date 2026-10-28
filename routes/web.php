<?php

use App\Livewire\Document\DocumentRegister;
use Illuminate\Support\Facades\Route;

Route::livewire('/', DocumentRegister::class)->name('register.document');
