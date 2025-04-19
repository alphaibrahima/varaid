<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AssociationDashboardController;
use App\Http\Controllers\AffiliationVerificationController;
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
    
    // Reçus de réservation (pas besoin d'affiliation vérifiée)
    Route::get('/reservation/receipt/{code}', [ReservationController::class, 'showReceipt'])
        ->name('reservation.receipt');
    Route::get('/reservation/receipt/{code}/download', [ReservationController::class, 'downloadReceipt'])
        ->name('reservation.receipt.download');
    
    // Routes de vérification d'affiliation (UI)
    Route::get('/verify-affiliation', [AffiliationVerificationController::class, 'show'])
        ->name('affiliation.verify');
    Route::post('/verify-affiliation', [AffiliationVerificationController::class, 'verify']);
    Route::post('/resend-affiliation-code', [AffiliationVerificationController::class, 'resend'])
        ->name('affiliation.resend');

    // Routes de vérification d'affiliation (AJAX)
    Route::post('/verify-affiliation-code', [ReservationController::class, 'verifyAffiliationCode'])
        ->name('affiliation.verify.ajax');
    // Renommé pour éviter les doublons
    Route::post('/ajax-resend-affiliation-code', [ReservationController::class, 'resendAffiliationCode'])
        ->name('affiliation.resend.ajax');
    
    // Routes de paiement
    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent'])
        ->name('payment.create-intent');
    Route::get('/payment-success', [PaymentController::class, 'handleSuccess'])
        ->name('payment.success');
    
    // Dashboard principal (utilisez DashboardController au lieu de ReservationController)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/tutorials', [TutorialController::class, 'index'])->name('tutorials');
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

// Protégez les routes de réservation avec le middleware d'affiliation
Route::middleware(['auth', 'web', 'verified.affiliation'])->group(function () {
    Route::get('/reservation', [ReservationController::class, 'index'])->name('reservation.index');
    Route::post('/reservation/confirm', [ReservationController::class, 'confirmReservation'])
        ->name('reservation.confirm');
    Route::get('/get-slots/{date}', [ReservationController::class, 'getSlots'])->name('get.slots');
});

// Route pour vérifier la limite de réservation
Route::get('/check-reservation-limit', [ReservationController::class, 'checkReservationLimit'])
    ->middleware(['auth'])
    ->name('reservation.check-limit');

// Route temporaire pour tester les clés Stripe
Route::get('/test-stripe-keys', function() {
    return [
        'public_key' => config('services.stripe.key'),
        'secret_key' => substr(config('services.stripe.secret'), 0, 10) . '...',
        'keys_exist' => !empty(config('services.stripe.key')) && !empty(config('services.stripe.secret'))
    ];
});

require __DIR__.'/auth.php';