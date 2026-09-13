<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @hasSection('meta')
        @yield('meta')
    @else
        <meta name="description" content="@yield('description', 'A focused, distraction-free video learning platform.')">
    @endif

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2148e6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FocusedTube">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" href="/icons/icon-192.png" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased">

    {{-- Site header --}}
    <x-site-header />

    {{-- Flash messages --}}
    @if (session('status') || session('error') || $errors->any())
        <div class="mx-auto max-w-6xl w-full px-4 pt-4">
            <x-flash />
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Site footer --}}
    <x-site-footer />

    {{-- Mobile bottom nav --}}
    {{--<x-mobile-nav /> --}}
    {{-- PWA install prompt --}}
    <div
        x-data="{
            deferred: null,
            visible: false,
            init() {
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferred = e;
                    this.visible = true;
                });
            },
            async install() {
                if (! this.deferred) return;
                this.deferred.prompt();
                await this.deferred.userChoice;
                this.visible = false;
                this.deferred = null;
            },
            dismiss() { this.visible = false; }
        }"
        x-show="visible"
        x-transition
        style="display:none;"
        class="fixed bottom-20 right-4 z-50 max-w-xs rounded-xl bg-slate-900 p-4 text-white shadow-lg md:bottom-4"
    >
        <p class="text-sm">Install FocusedTube for a distraction-free experience.</p>
        <div class="mt-3 flex items-center gap-2">
            <button @click="install" class="rounded-md bg-white px-3 py-1.5 text-xs font-medium text-slate-900">Install</button>
            <button @click="dismiss" class="text-xs text-slate-400 hover:text-white">Not now</button>
        </div>
    </div>

    {{-- Service worker (production only) --}}
    @production
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js').catch(function (e) {
                        console.warn('SW registration failed', e);
                    });
                });
            }
        </script>
    @endproduction

    @stack('scripts')
</body>
</html>