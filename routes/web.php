<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
|
| Everything a guest or end-user can reach: home, video browsing,
| categories, playlists, search, watchlist, history, and the watch-
| tracking endpoints. Defined in routes/public.php and included here.
|
*/

require __DIR__.'/public.php';

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)
            ->name('dashboard');

        /* ---------------- Videos ---------------- */

        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get('/',              [\App\Http\Controllers\Admin\VideoController::class, 'index'])->name('index');
            Route::post('/bulk',         [\App\Http\Controllers\Admin\VideoController::class, 'bulk'])->name('bulk');

            // Add flow
            Route::get('/create',        [\App\Http\Controllers\Admin\VideoController::class, 'create'])->name('create');
            Route::post('/fetch',        [\App\Http\Controllers\Admin\VideoController::class, 'fetch'])->name('fetch');
            Route::get('/create/review', [\App\Http\Controllers\Admin\VideoController::class, 'review'])->name('review');
            Route::delete('/create',     [\App\Http\Controllers\Admin\VideoController::class, 'cancelFetch'])->name('cancel');
            Route::post('/',             [\App\Http\Controllers\Admin\VideoController::class, 'store'])->name('store');

            // Resource (literal segments declared before {video})
            Route::get('/{video}/edit',       [\App\Http\Controllers\Admin\VideoController::class, 'edit'])->name('edit');
            Route::patch('/{video}',          [\App\Http\Controllers\Admin\VideoController::class, 'update'])->name('update');
            Route::delete('/{video}',         [\App\Http\Controllers\Admin\VideoController::class, 'destroy'])->name('destroy');
            Route::post('/{video}/publish',   [\App\Http\Controllers\Admin\VideoController::class, 'publish'])->name('publish');
            Route::post('/{video}/unpublish', [\App\Http\Controllers\Admin\VideoController::class, 'unpublish'])->name('unpublish');
            Route::post('/{video}/archive',   [\App\Http\Controllers\Admin\VideoController::class, 'archive'])->name('archive');
            Route::post('/{video}/restore',   [\App\Http\Controllers\Admin\VideoController::class, 'restore'])->name('restore');
        });

        /* ---------------- Categories ---------------- */

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::get('/create',          [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
            Route::post('/',               [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
            Route::patch('/{category}',    [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}',   [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
        });

        /* ---------------- Playlists ---------------- */

        Route::prefix('playlists')->name('playlists.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\PlaylistController::class, 'index'])->name('index');
            Route::get('/create',          [\App\Http\Controllers\Admin\PlaylistController::class, 'create'])->name('create');
            Route::post('/',               [\App\Http\Controllers\Admin\PlaylistController::class, 'store'])->name('store');
            Route::get('/{playlist}/edit', [\App\Http\Controllers\Admin\PlaylistController::class, 'edit'])->name('edit');
            Route::patch('/{playlist}',    [\App\Http\Controllers\Admin\PlaylistController::class, 'update'])->name('update');
            Route::delete('/{playlist}',   [\App\Http\Controllers\Admin\PlaylistController::class, 'destroy'])->name('destroy');

            // Video management within a playlist
            Route::post('/{playlist}/videos',           [\App\Http\Controllers\Admin\PlaylistVideoController::class, 'attach'])->name('videos.attach');
            Route::delete('/{playlist}/videos/{video}', [\App\Http\Controllers\Admin\PlaylistVideoController::class, 'detach'])->name('videos.detach');
            Route::post('/{playlist}/reorder',          [\App\Http\Controllers\Admin\PlaylistVideoController::class, 'reorder'])->name('reorder');
        });

        /* ---------------- Placeholders (route:cache friendly) ---------------- */

        Route::view('/users',      'admin.placeholder')->name('users.index')->defaults('title', 'Users');
        Route::view('/audit-logs', 'admin.placeholder')->name('audit-logs.index')->defaults('title', 'Audit Logs');
        Route::view('/settings',   'admin.placeholder')->name('settings.index')->defaults('title', 'Settings');
    });

/*
|--------------------------------------------------------------------------
| Auth routes (login, register, password reset, email verification)
|--------------------------------------------------------------------------
*/

Route::get('/__debug/add-video', function () {
    $service = app(\App\Services\YouTube\YouTubeService::class);

    try {
        $data = $service->fetchVideo('dQw4w9WgXcQ');
    } catch (\Throwable $e) {
        return response()->json([
            'step'  => 'fetch',
            'error' => get_class($e).': '.$e->getMessage(),
        ]);
    }

    try {
        $admin = \App\Models\User::factory()->admin()->create();
        $videoService = app(\App\Services\Video\VideoService::class);

        $video = $videoService->createFromYouTube(
            $data,
            ['status' => 'draft', 'visibility' => 'public'],
            $admin,
        );

        return response()->json([
            'step'     => 'create',
            'video_id' => $video->id,
            'title'    => $video->title,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'step'  => 'create',
            'error' => get_class($e).': '.$e->getMessage(),
            'trace' => collect($e->getTrace())->take(5)->map(fn ($f) => ($f['class'] ?? '').'::'.($f['function'] ?? ''))->all(),
        ]);
    }
});

require __DIR__.'/auth.php';