<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

    {{-- Sidebar backdrop (mobile) --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
        style="display: none;"
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-200 transition-transform lg:static lg:translate-x-0"
    >
        <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-5">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 font-semibold text-white">F</span>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-white">FocusedTube</span>
                <span class="text-xs text-slate-400">Admin Panel</span>
            </div>
        </div>

        <nav class="px-3 py-4 space-y-1 text-sm">
            <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                Dashboard
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.videos.index')" :active="request()->routeIs('admin.videos.*')">
                Videos
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                Categories
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.playlists.index')" :active="request()->routeIs('admin.playlists.*')">
                Playlists
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                Users
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                Audit Logs
            </x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                Settings
            </x-admin.nav-link>
        </nav>

        <div class="mt-auto border-t border-slate-800 px-3 py-3 text-xs text-slate-400">
            <div class="px-3 pb-2">
                Signed in as <span class="text-slate-200">{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-md px-3 py-2 text-left hover:bg-slate-800">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex min-h-screen flex-1 flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:px-8">
            <button
                @click="sidebarOpen = true"
                class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                aria-label="Open navigation"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>

            <div class="flex flex-1 items-center gap-3">
                <h1 class="text-base font-semibold text-slate-900">
                    @yield('heading', 'Admin')
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="btn-ghost">View site</a>
            </div>
        </header>

        {{-- Breadcrumbs --}}
        @hasSection('breadcrumbs')
            <div class="border-b border-slate-200 bg-white px-4 py-3 lg:px-8">
                <nav class="text-sm text-slate-500" aria-label="Breadcrumb">
                    @yield('breadcrumbs')
                </nav>
            </div>
        @endif

        {{-- Flash --}}
        <div class="px-4 pt-4 lg:px-8">
            <x-flash />
        </div>

        {{-- Content --}}
        <main class="flex-1 px-4 py-6 lg:px-8">
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-4 py-4 text-xs text-slate-500 lg:px-8">
            FocusedTube Admin
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>