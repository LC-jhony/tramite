<?php

use App\Livewire\CaseTrackingForm;
use App\Livewire\DocumentForm;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', DocumentForm::class)->name('document.form');
Route::get('/case-tracking', CaseTrackingForm::class)->name('case.tracking.form');
