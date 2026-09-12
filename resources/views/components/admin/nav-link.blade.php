@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @class([
        'block rounded-md px-3 py-2 font-medium transition',
        'bg-brand-600 text-white' => $active,
        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    {{ $slot }}
</a>