<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Auth; 
// Désactiver les routes de vérification email par défaut






// Routes authentifiées
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

require __DIR__.'/auth.php';