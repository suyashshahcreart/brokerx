<!DOCTYPE html>
<html lang="en">

<head>
    @include('frontend.layouts.partials/title-meta', ['title' => $title])
    @include('frontend.layouts.partials/head-css')
    @yield('css')
</head>

<body>

<div class="wrapper">

    @include("frontend.layouts.partials/topbar")
    @include("frontend.layouts.partials/main-nav")

    <div class="page-content">

        <div class="container-fluid">

            @include("frontend.layouts.partials/page-title")
            @include('frontend.layouts.partials.alerts')

            @yield('content')

        </div>

        @include("frontend.layouts.partials/footer")

        @yield('modal')
    </div>

</div>

@include("frontend.layouts.partials/right-sidebar")
@include('frontend.layouts.partials/footer-scripts')

</body>

</html>
