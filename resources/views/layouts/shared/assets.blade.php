{!! moonshineAssets()->css() !!}

@yield('after-styles')

@stack('styles')

{!! moonshineAssets()->js() !!}

@yield('after-scripts')

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    const translates = @js(__('moonshine::ui'));
</script>
