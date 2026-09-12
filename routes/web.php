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

        Route::get('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'index'])->name('videos.index');
        Route::get('/videos/create', fn () => view('admin.placeholder', ['title' => 'Add Video']))
            ->name('videos.create');

        Route::get('/categories', fn () => view('admin.placeholder', ['title' => 'Categories']))
            ->name('categories.index');

        Route::get('/playlists', fn () => view('admin.placeholder', ['title' => 'Playlists']))
            ->name('playlists.index');

        Route::get('/users', fn () => view('admin.placeholder', ['title' => 'Users']))
            ->name('users.index');

        Route::get('/audit-logs', fn () => view('admin.placeholder', ['title' => 'Audit Logs']))
            ->name('audit-logs.index');

        Route::get('/settings', fn () => view('admin.placeholder', ['title' => 'Settings']))
            ->name('settings.index');
    });

require __DIR__.'/auth.php';