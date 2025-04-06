<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Auth; 
// Désactiver les routes de vérification email par défaut

// Routes authentifiées
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/reservation/confirm', [ReservationController::class, 'confirmReservation'])
        ->name('reservation.confirm');
    Route::get('/reservation/receipt/{code}', [ReservationController::class, 'showReceipt'])
        ->name('reservation.receipt');
    Route::get('/reservation/receipt/{code}/download', [ReservationController::class, 'downloadReceipt'])
        ->name('reservation.receipt.download');
});

// Routes d'authentification
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

// Routes de base
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ReservationController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/get-slots/{date}', [ReservationController::class, 'getSlots'])->name('get.slots');


Route::middleware(['auth', 'web'])->group(function () {
    // Routes existantes...
    Route::post('/create-payment-intent', [App\Http\Controllers\PaymentController::class, 'createPaymentIntent'])
        ->name('payment.create-intent');
});

Route::get('/payment-success', [App\Http\Controllers\PaymentController::class, 'handleSuccess'])
    ->name('payment.success')
    ->middleware('auth');

// Dans routes/web.php
// Route temporaire pour tester les clés Stripe
Route::get('/test-stripe-keys', function() {
    return [
        'public_key' => config('services.stripe.key'),
        'secret_key' => substr(config('services.stripe.secret'), 0, 10) . '...',
        'keys_exist' => !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'))
    ];
});

require __DIR__.'/auth.php';