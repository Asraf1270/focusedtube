@if (session('status'))
    <div class="mb-3 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
        <p class="font-medium">Please fix the following:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif