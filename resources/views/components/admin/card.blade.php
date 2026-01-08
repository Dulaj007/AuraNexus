<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow p-4']) }}>
    @if(isset($title))
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-lg">{{ $title }}</h2>
            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</div>
