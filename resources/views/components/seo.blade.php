@props([
    'title'       => config('app.name'),
    'description' => null,
    'image'       => null,
    'type'        => 'website',
    'canonical'   => null,
])

@php
    $fullTitle  = $title === config('app.name') ? $title : "{$title} — ".config('app.name');
    $canonical ??= url()->current();
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description ?? 'A focused, distraction-free video learning platform.' }}">

<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description ?? '' }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description ?? '' }}">
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif