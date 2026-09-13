<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-slate-900">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-white">F</span>
            <span>FocusedTube</span>
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm">
            <a href="{{ route('videos.index') }}" class="rounded-md px-3 py-2 hover:bg-slate-100">Videos</a>
            <a href="{{ route('categories.index') }}" class="rounded-md px-3 py-2 hover:bg-slate-100">Categories</a>
            <a href="{{ route('playlists.index') }}" class="rounded-md px-3 py-2 hover:bg-slate-100">Playlists</a>
            <a href="{{ route('search') }}" class="rounded-md px-3 py-2 hover:bg-slate-100">Search</a>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            @auth
                <a href="{{ route('watchlist.index') }}" class="hidden sm:inline-flex btn-ghost">Saved</a>
                <a href="{{ route('history.index') }}" class="hidden sm:inline-flex btn-ghost">History</a>
                <a href="{{ route('profile.show') }}" class="btn-ghost">{{ auth()->user()->name }}</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-ghost">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Sign up</a>
            @endauth
        </div>
    </div>
</header>