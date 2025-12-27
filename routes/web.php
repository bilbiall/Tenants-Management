<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

//generic login
//Route::get('/login', fn () => view('generic-login'))->name('generic.login');
//Route::post('/login', \App\Http\Controllers\GenericLoginController::class)->name('generic.login.attempt');
// routes/web.php
Route::get('/login', fn () => view('generic-login'))->name('generic.login');
Route::post('/login', \App\Http\Controllers\GenericLoginController::class)
     ->name('generic.login.attempt');

use App\Http\Controllers\PesapalController;

// Tenant payment initiation (Pesapal scaffold)
Route::middleware(['auth'])->group(function () {
    Route::get('/tenant/payments/initiate/{invoice}', [PesapalController::class, 'initiate'])
        ->name('tenant.payments.initiate');
    // Legacy simulation endpoint for local testing (protected by auth)
    Route::get('/tenant/payments/pesapal-callback', [PesapalController::class, 'simulateCallback'])
        ->name('tenant.payments.pesapal.callback');
});


