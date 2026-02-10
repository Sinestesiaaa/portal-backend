<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DocumentAuditController;
use App\Http\Controllers\SiteController;
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

Route::get('/documents/preview-description/{id}', [DocumentController::class, 'previewDescription'])
    ->middleware('auth')
    ->name('documents.preview_description');

// =====================================
// DOCUMENT LIST (ALL ROLES)
// =====================================
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/numbers', [DocumentController::class, 'numberList'])
        ->name('documents.numbers');
    Route::get('/documents/review', [DocumentController::class, 'reviewList'])
        ->name('documents.review');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/autocomplete', [DocumentController::class, 'autocomplete'])
        ->name('documents.autocomplete');
    Route::get('/documents/export', [DocumentController::class, 'exportPage'])
        ->name('documents.export_page');
    Route::get('/documents/export-csv', [DocumentController::class, 'export'])
        ->name('documents.export_csv');
    Route::post('/documents/export-selected', [DocumentController::class, 'exportSelected'])
        ->name('documents.export_selected');
    Route::get('/documents/export-pdf', [DocumentController::class, 'exportPdf'])
        ->name('documents.export_pdf');
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

    Route::post('/{id}/related', [DocumentController::class, 'updateRelated'])
        ->name('documents.related.update');
    Route::delete('/{id}/related/{relatedId}', [DocumentController::class, 'deleteRelated'])
        ->name('documents.related.delete');
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
        Route::resource('departments', DepartmentController::class);
        Route::resource('sites', SiteController::class)->except(['show']);
        Route::resource('document-types', DocumentTypeController::class);
        Route::get('audits', [DocumentAuditController::class, 'index'])->name('audits.index');
    });

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');



require __DIR__ . '/auth.php';
