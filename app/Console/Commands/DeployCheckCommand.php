<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeployCheckCommand extends Command
{
    protected $signature   = 'focus:deploy-check';
    protected $description = 'Verify production readiness before deploying FocusedTube.';

    private array $errors   = [];
    private array $warnings = [];

    public function handle(): int
    {
        $this->line('');
        $this->line('FocusedTube — pre-deploy checks');
        $this->line('');

        $this->checkEnvironment();
        $this->checkAppKey();
        $this->checkUrl();
        $this->checkDebug();
        $this->checkSession();
        $this->checkDatabase();
        $this->checkYouTubeKey();
        $this->checkStorageSymlink();
        $this->checkWritablePaths();
        $this->checkSeededAdminPassword();
        $this->checkCaches();
        $this->checkPwaAssets();

        foreach ($this->warnings as $w) {
            $this->warn('  ⚠  '.$w);
        }
        foreach ($this->errors as $e) {
            $this->error('  ✗  '.$e);
        }

        $this->line('');

        if (! empty($this->errors)) {
            $this->error(count($this->errors).' blocking issue(s). Fix these before deploying.');
            return self::FAILURE;
        }

        $this->info('  ✓  All checks passed. Ready to deploy.');
        if (! empty($this->warnings)) {
            $this->warn('  ⚠  '.count($this->warnings).' non-blocking warning(s).');
        }

        return self::SUCCESS;
    }

    private function checkEnvironment(): void
    {
        if (app()->environment('production')) {
            $this->ok('APP_ENV is production');
        } else {
            $this->warnings[] = "APP_ENV is '".app()->environment()."'. Not a blocker, but only meaningful on production.";
        }
    }

    private function checkAppKey(): void
    {
        if (empty(config('app.key'))) {
            $this->errors[] = 'APP_KEY is empty. Run: php artisan key:generate';
        } else {
            $this->ok('APP_KEY is set');
        }
    }

    private function checkUrl(): void
    {
        $url = config('app.url');
        if (! $url || str_starts_with($url, 'http://') && app()->environment('production')) {
            $this->warnings[] = "APP_URL is '{$url}'. Production should be https://…";
        } else {
            $this->ok('APP_URL is '.$url);
        }
    }

    private function checkDebug(): void
    {
        if (config('app.debug') && app()->environment('production')) {
            $this->errors[] = 'APP_DEBUG is true in production. Exposes stack traces and secrets.';
        } else {
            $this->ok('APP_DEBUG is '.config('app.debug') ? 'true' : 'false');
        }
    }

    private function checkSession(): void
    {
        if (app()->environment('production')) {
            if (! config('session.secure')) {
                $this->warnings[] = 'SESSION_SECURE_COOKIE is off. Cookies will be sent over plain HTTP.';
            } else {
                $this->ok('Secure session cookies enabled');
            }

            if (! config('session.encrypt')) {
                $this->warnings[] = 'SESSION_ENCRYPT is off. Recommended in production.';
            } else {
                $this->ok('Session encryption enabled');
            }
        }
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $this->ok('Database connection OK');
        } catch (\Throwable $e) {
            $this->errors[] = 'Database connection failed: '.$e->getMessage();
        }
    }

    private function checkYouTubeKey(): void
    {
        if (empty(config('services.youtube.key'))) {
            $this->errors[] = 'YOUTUBE_API_KEY is not set. Admins cannot add videos.';
        } else {
            $this->ok('YouTube API key is set');
        }
    }

    private function checkStorageSymlink(): void
    {
        if (! File::exists(public_path('storage'))) {
            $this->warnings[] = 'Storage symlink missing. Run: php artisan storage:link';
        } else {
            $this->ok('Storage symlink present');
        }
    }

    private function checkWritablePaths(): void
    {
        foreach (['storage', 'storage/framework', 'storage/framework/cache', 'storage/logs', 'bootstrap/cache'] as $dir) {
            $path = base_path($dir);
            if (! is_dir($path) || ! is_writable($path)) {
                $this->errors[] = "Path not writable: {$dir}";
            }
        }
        $this->ok('Storage and cache paths are writable');
    }

    private function checkSeededAdminPassword(): void
    {
        $admin = \App\Models\User::where('email', 'admin@focusedtube.test')->first();
        if (! $admin) {
            $this->ok('Seeded admin account does not exist');
            return;
        }

        if (\Illuminate\Support\Facades\Hash::check('password', $admin->password)) {
            $this->errors[] = 'The seeded admin account still uses the default password. Run: php artisan focus:lock-admin';
        } else {
            $this->ok('Seeded admin password has been rotated');
        }
    }

    private function checkCaches(): void
    {
        // Config cache files exist?
        if (app()->environment('production')) {
            if (! File::exists(base_path('bootstrap/cache/config.php'))) {
                $this->warnings[] = 'Config not cached. Run: php artisan config:cache';
            } else {
                $this->ok('Config cache present');
            }
        }
    }

    private function checkPwaAssets(): void
    {
        foreach (['manifest.json', 'sw.js', 'icons/icon-192.png', 'icons/icon-512.png'] as $asset) {
            if (! File::exists(public_path($asset))) {
                $this->warnings[] = "Missing PWA asset: public/{$asset}";
            }
        }
        $this->ok('PWA asset check complete');
    }

    private function ok(string $message): void
    {
        $this->line('  <fg=green>✓</>  '.$message);
    }
}