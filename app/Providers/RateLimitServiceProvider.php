<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $key = mb_strtolower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'email' => 'Too many login attempts. Please try again in a minute.',
                ])->onlyInput('email');
            });
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('progress', function (Request $request) {
            // Generous: one update every ~5 seconds is fine, but a well-behaved
            // client sends at most ~10/min. Cap at 60/min per user to be safe
            // against bursts and stuck tabs.
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}