<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect ke login
Route::get('/', fn() => redirect()->route('login'));


// =====================================
// DASHBOARD
// =====================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// =====================================
// PROFILE (Semua user login)
// =====================================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// =====================================
// DOCUMENT — READ (ALL ROLES)
// =====================================
Route::middleware(['auth'])->group(function () {

    // halaman daftar dokumen
    Route::get('/documents', [DocumentController::class, 'index'])
        ->name('documents.index');

    // halaman detail dokumen
    Route::get('/documents/show/{id}', [DocumentController::class, 'show'])
        ->name('documents.show');
});


// =====================================
// DOCUMENT — CRUD (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])->prefix('documents')->group(function () {

    Route::get('/create', [DocumentController::class, 'create'])
        ->name('documents.create');

    Route::post('/', [DocumentController::class, 'store'])
        ->name('documents.store');

    Route::get('/edit/{id}', [DocumentController::class, 'edit'])
        ->name('documents.edit');

    Route::put('/{id}', [DocumentController::class, 'update'])
        ->name('documents.update');

    Route::delete('/{id}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');
});


// =====================================
// USER MANAGEMENT (ADMIN ONLY)
// =====================================
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('users', UserController::class);
        // menghasilkan:
        // admin.users.index
        // admin.users.create
        // admin.users.store
        // admin.users.edit
        // admin.users.update
        // admin.users.destroy
    });


// Route auth (login, register, forgot password)
require __DIR__ . '/auth.php';
