@props(['category'])

<a href="{{ route('categories.show', $category) }}"
   class="group flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md hover:ring-brand-200">
    <span class="grid h-10 w-10 flex-none place-items-center rounded-lg bg-brand-50 text-lg">
        {{ $category->icon ?: '•' }}
    </span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-slate-900 group-hover:text-brand-700">{{ $category->name }}</p>
        <p class="text-xs text-slate-500">{{ $category->videos_count ?? 0 }} videos</p>
    </div>
    <span class="text-slate-300 group-hover:text-brand-500" aria-hidden="true">→</span>
</a>