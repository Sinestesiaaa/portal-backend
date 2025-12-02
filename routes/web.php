<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect ke login
Route::get('/', fn() => redirect()->route('login'));

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// PROFILE
Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =====================================
// DOCUMENT — VIEW (ALL ROLES)
// =====================================
Route::middleware(['auth'])->group(function () {

    // LIST
    Route::get('/documents', [DocumentController::class, 'index'])
        ->name('documents.index');
});

// =====================================
// DOCUMENT — CRUD (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])->prefix('documents')->group(function () {

    // CREATE HARUS DITULIS SEBELUM /documents/{id}
    Route::get('/create', [DocumentController::class, 'create'])
        ->name('documents.create');

    Route::post('/', [DocumentController::class, 'store'])
        ->name('documents.store');

    // EDIT
    Route::get('/{id}/edit', [DocumentController::class, 'edit'])
        ->name('documents.edit');

    // UPDATE
    Route::put('/{id}', [DocumentController::class, 'update'])
        ->name('documents.update');

    // DELETE
    Route::delete('/{id}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');
});

// =====================================
// DETAIL (RESTFUL)
// =====================================
Route::middleware(['auth'])->group(function () {

    Route::get('/documents/{id}', [DocumentController::class, 'show'])
        ->name('documents.show');
});


// =====================================
// USER MANAGEMENT (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {

        Route::resource('users', UserController::class);
    });

require __DIR__ . '/auth.php';
