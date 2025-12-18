
<script>
    // Base path for proppik theme assets (used by proppik/assets/js/main.js)
    window.__PROPPIK_ASSET_BASE = @json(rtrim(asset('proppik'), '/') . '/');
</script>

@yield('script-bottom')
{{-- Only load Vite for admin/backend views, not frontend --}}
@php
    $isFrontendRoute = request()->is('front*') 
        || request()->is('setup*') 
        || request()->is('login')
        || request()->is('booking-dashboard*')
        || request()->is('booking/*')
        || request()->is('profile*')
        || request()->routeIs('frontend.*');
@endphp
@if(!$isFrontendRoute)
    @vite(['resources/js/app.js','resources/js/layout.js'])
@endif
