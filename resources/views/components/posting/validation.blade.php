@props(['errors'])

<div class="mb-4 rounded border border-red-600/30 bg-red-600/10 px-4 py-3 text-red-200">
    <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
