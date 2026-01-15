@inject('theme', \App\Services\ThemeService::class)

@php
  $mode = request()->cookie('theme_mode', 'dark');
@endphp

<style>
{!! $theme->css($mode) !!}
</style>

<script>
  // Ensure <html> has data-theme set (no flash)
  (function () {
    var mode = @json($mode);
    document.documentElement.setAttribute('data-theme', mode);
  })();
</script>
