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
use App\Http\Controllers\MpesaController;

// Tenant payment initiation (Pesapal and M-Pesa)
Route::middleware(['auth'])->group(function () {
    // Pesapal routes
    Route::get('/tenant/payments/initiate/{invoice}', [PesapalController::class, 'initiate'])
        ->name('tenant.payments.initiate');
    Route::get('/tenant/payments/pesapal-callback', [PesapalController::class, 'simulateCallback'])
        ->name('tenant.payments.pesapal.callback');
    Route::get('/payments/pesapal/callback', [PesapalController::class, 'callbackRedirect'])
        ->name('payments.pesapal.callback.redirect');
    
    // M-Pesa routes
    // Allow GET for Filament redirects and POST for direct form submissions
    Route::match(['get', 'post'], '/tenant/mpesa/initiate/{invoice}', [MpesaController::class, 'initiate'])
        ->name('tenant.mpesa.initiate');
    Route::get('/tenant/mpesa/status', [MpesaController::class, 'checkStatus'])
        ->name('tenant.mpesa.status');
    Route::get('/mpesa/callback/redirect', [MpesaController::class, 'callbackRedirect'])
        ->name('mpesa.callback.redirect');
});


