<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
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
// PREVIEW PDF (HARUS DI ATAS /documents/{id})
// =====================================
Route::get('/documents/preview/{id}', [DocumentController::class, 'preview'])
    ->middleware('auth')
    ->name('documents.preview');

// =====================================
// DOCUMENT LIST (ALL ROLES)
// =====================================
Route::middleware(['auth'])->group(function () {
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
});

// =====================================
// DOCUMENT CRUD (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])->prefix('documents')->group(function () {

    Route::get('/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/', [DocumentController::class, 'store'])->name('documents.store');

    Route::get('/{id}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/{id}', [DocumentController::class, 'update'])->name('documents.update');

    Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});

// =====================================
// SHOW DOCUMENT DETAIL
// =====================================
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');
});

// =====================================
// USER MANAGEMENT (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class);
    });

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');



require __DIR__ . '/auth.php';
