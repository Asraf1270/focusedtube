@props(['headers' => []])

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-4 py-3 font-medium">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>