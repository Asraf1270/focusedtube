<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages.home'))->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
    });

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

        Route::prefix('videos')->name('videos.')->group(function () {
    Route::get('/',              [VideoController::class, 'index'])->name('index');
    Route::post('/bulk',         [VideoController::class, 'bulk'])->name('bulk');

    // Add flow
    Route::get('/create',        [VideoController::class, 'create'])->name('create');
    Route::post('/fetch',        [VideoController::class, 'fetch'])->name('fetch');
    Route::get('/create/review', [VideoController::class, 'review'])->name('review');
    Route::delete('/create',     [VideoController::class, 'cancelFetch'])->name('cancel');
    Route::post('/',             [VideoController::class, 'store'])->name('store');

    // Resource (order matters — literal segments before {video})
    Route::get('/{video}/edit',        [VideoController::class, 'edit'])->name('edit');
    Route::patch('/{video}',           [VideoController::class, 'update'])->name('update');
    Route::delete('/{video}',          [VideoController::class, 'destroy'])->name('destroy');
    Route::post('/{video}/publish',    [VideoController::class, 'publish'])->name('publish');
    Route::post('/{video}/unpublish',  [VideoController::class, 'unpublish'])->name('unpublish');
    Route::post('/{video}/archive',    [VideoController::class, 'archive'])->name('archive');
    Route::post('/{video}/restore',    [VideoController::class, 'restore'])->name('restore');
});

        // placeholders from Step 4 remain unchanged below…
    });

require __DIR__.'/auth.php';