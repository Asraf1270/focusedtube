<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="mx-auto flex min-h-screen max-w-xl flex-col items-center justify-center px-6 text-center">
        <a href="/" class="flex items-center gap-2 font-semibold text-slate-900">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-600 text-white">F</span>
            FocusedTube
        </a>

        <div class="mt-10 text-6xl font-semibold text-slate-900">@yield('code')</div>
        <h1 class="mt-3 text-xl font-medium text-slate-900">@yield('title')</h1>
        <p class="mt-3 text-slate-600">@yield('message')</p>

        <div class="mt-8 flex items-center gap-3">
            <a href="/" class="btn-primary">Back to home</a>
            @auth
                <a href="{{ route('videos.index') }}" class="btn-ghost">Browse videos</a>
            @endauth
        </div>
    </div>
</body>
</html>