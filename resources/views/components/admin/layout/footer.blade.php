<footer class="border-t"
        style="border-color: var(--an-border); background: color-mix(in srgb, var(--an-card) 70%, transparent); backdrop-filter: blur(12px);">
    <div class="px-4 sm:px-6 py-4 text-xs flex flex-wrap items-center justify-between gap-2"
         style="color: var(--an-text-muted);">
        <div>
            © {{ date('Y') }} {{ config('app.name', 'AuraNexus') }} 
        </div>
        <div>
            v1.0 
        </div>
    </div>
</footer>
