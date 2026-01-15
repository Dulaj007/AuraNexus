<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title', config('app.name'))</title>

    @yield('meta')

    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>[x-cloak]{display:none !important;}</style>

</head>
<body class="bg-zinc-950 text-white">
    @include('partials.nav')

    <main class="max-w-6xl mx-auto px-4 py-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
    <script>
document.addEventListener('DOMContentLoaded', () => {
  let openModalId = null;

  function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
    openModalId = id;
  }

  function closeModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
    if (openModalId === id) openModalId = null;
  }

  // Open buttons
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-modal-open]');
    if (!btn) return;
    openModal(btn.getAttribute('data-modal-open'));
  });

  // Close buttons + backdrop
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-modal-close]');
    if (!btn) return;
    closeModal(btn.getAttribute('data-modal-close'));
  });

  // ESC to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && openModalId) closeModal(openModalId);
  });
});
</script>

</body>
</html>
