@yield('head_css')

{{-- Avoid loading admin Vite styles/scripts on frontend pages (it overrides navbar/theme CSS) --}}
@php
    $isFrontendRoute = request()->is('front*')
        || request()->is('/')
        || request()->is('setup*')
        || request()->is('login*')
        || request()->is('booking-dashboard*')
        || request()->is('booking/*')
        || request()->is('profile*')
        || request()->is('contact*')
        || request()->routeIs('frontend.*');
@endphp

@if(!$isFrontendRoute)
    @vite(['resources/scss/icons.scss','resources/scss/app.scss'])
    @vite(['resources/js/config.js'])
@endif
