<!DOCTYPE html>
<html lang="en" @yield('html_attribute')>

<head>
    @include('frontend.layouts.partials/title-meta', ['title' => $title])
    @include('frontend.layouts.partials/head-css')
    @yield('css')
</head>

<body>

@include('frontend.layouts.partials.header')

@yield('content')

@include('frontend.layouts.partials.footer')

@include('frontend.layouts.partials/footer-scripts')

</body>

</html>
