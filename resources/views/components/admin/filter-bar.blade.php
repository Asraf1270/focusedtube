@props(['action' => null, 'method' => 'GET'])

<form method="{{ $method }}" action="{{ $action ?? url()->current() }}"
      class="card flex flex-col gap-3 p-4 md:flex-row md:items-end">
    {{ $slot }}
</form>