@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 px-4 py-3 text-sm" role="navigation">
        <div class="text-slate-500">
            Showing <span class="font-medium text-slate-700">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-slate-700">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-slate-700">{{ $paginator->total() }}</span>
        </div>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-ghost opacity-50 pointer-events-none">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-ghost" rel="prev">Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-slate-400">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-md bg-brand-600 px-3 py-1.5 font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded-md px-3 py-1.5 text-slate-700 hover:bg-slate-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-ghost" rel="next">Next</a>
            @else
                <span class="btn-ghost opacity-50 pointer-events-none">Next</span>
            @endif
        </div>
    </nav>
@endif