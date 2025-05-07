<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AssociationDashboardController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

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

// Routes protégées par authentification simple
Route::middleware(['auth', 'web'])->group(function () {
    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Reçus de réservation
    Route::get('/reservation/receipt/{code}', [ReservationController::class, 'showReceipt'])
        ->name('reservation.receipt');
    Route::get('/reservation/receipt/{code}/download', [ReservationController::class, 'downloadReceipt'])
        ->name('reservation.receipt.download');
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/tutorials', [TutorialController::class, 'index'])->name('tutorials');
    Route::get('/aide-faq', [App\Http\Controllers\HelpController::class, 'index'])->name('help.index');
    Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts.index');
});

// Routes pour le dashboard des associations
Route::middleware(['auth', 'web'])->prefix('association')->name('association.')->group(function () {
    Route::get('/dashboard', [AssociationDashboardController::class, 'index'])->name('dashboard');
    Route::get('/buyers', [AssociationDashboardController::class, 'buyers'])->name('buyers');
    Route::get('/buyers/{id}', [AssociationDashboardController::class, 'buyerDetails'])->name('buyers.details');
    Route::post('/buyers/{id}/toggle-status', [AssociationDashboardController::class, 'toggleBuyerStatus'])->name('buyers.toggle-status');
    Route::get('/reservations', [AssociationDashboardController::class, 'reservations'])->name('reservations');
    Route::get('/quotas', [AssociationDashboardController::class, 'quotas'])->name('quotas');
});

// Protégez les routes de réservation avec le middleware d'authentification normal
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/reservation', [ReservationController::class, 'index'])->name('reservation.index');
    Route::post('/reservation/confirm', [ReservationController::class, 'confirmReservation'])
        ->name('reservation.confirm');
    Route::get('/get-slots/{date}', [ReservationController::class, 'getSlots'])->name('get.slots');
});

// Route pour vérifier la limite de réservation
Route::get('/check-reservation-limit', [ReservationController::class, 'checkReservationLimit'])
    ->middleware(['auth'])
    ->name('reservation.check-limit');

require __DIR__.'/auth.php';