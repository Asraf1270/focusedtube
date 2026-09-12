@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default', // default | success | warning | danger
])

@php
    $toneClasses = [
        'default' => 'text-slate-900',
        'success' => 'text-emerald-600',
        'warning' => 'text-amber-600',
        'danger'  => 'text-red-600',
    ][$tone] ?? 'text-slate-900';
@endphp

<div class="card p-5">
    <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
    <div class="mt-2 text-2xl font-semibold {{ $toneClasses }}">{{ $value }}</div>
    @if ($hint)
        <div class="mt-1 text-xs text-slate-500">{{ $hint }}</div>
    @endif
</div>