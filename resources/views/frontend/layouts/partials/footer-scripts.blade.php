@yield('script')
@yield('script-bottom')
{{-- Only load Vite for admin/backend views, not frontend --}}
@if(!request()->is('front*') && !request()->is('setup*'))
    @vite(['resources/js/app.js','resources/js/layout.js'])
@endif
