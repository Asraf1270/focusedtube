<?php

use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HistoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PlaylistController;
use App\Http\Controllers\Public\ProgressController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\StartedController;
use App\Http\Controllers\Public\VideoController;
use App\Http\Controllers\Public\WatchlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Fully public (guest + authenticated)
|--------------------------------------------------------------------------
*/

Route::get('/',              HomeController::class)->name('home');
Route::get('/offline',       fn () => view('pages.offline'))->name('offline');

/* ---- Videos ---- */

Route::get('/videos',         [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');

/* ---- Categories ---- */

Route::get('/categories',            [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

/* ---- Playlists ---- */

Route::get('/playlists',             [PlaylistController::class, 'index'])->name('playlists.index');
Route::get('/playlists/{playlist}',  [PlaylistController::class, 'show'])->name('playlists.show');

/* ---- Search ---- */

Route::get('/search', SearchController::class)->name('search');

/* ---- SEO / machine-readable ---- */

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt',  RobotsController::class)->name('robots');

/*
|--------------------------------------------------------------------------
| Authenticated (and active) users only
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    /* ---- Watchlist ---- */

    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/videos/{video}/save',   [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/videos/{video}/save', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');

    /* ---- Watch tracking ---- */

    Route::post('/videos/{video}/started', StartedController::class)
        ->name('videos.started');

    Route::post('/videos/{video}/progress', ProgressController::class)
        ->middleware('throttle:progress')
        ->name('videos.progress');

    /* ---- Watch history ---- */

    Route::get('/history',             [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/history/{entry}',  [HistoryController::class, 'destroy'])->name('history.destroy');
    Route::delete('/history',          [HistoryController::class, 'clear'])->name('history.clear');
});