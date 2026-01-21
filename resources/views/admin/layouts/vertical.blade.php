<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.layouts.partials/title-meta', ['title' => $title])
    @include('admin.layouts.partials/head-css')
</head>

<body>

    <div class="wrapper">

        @include("admin.layouts.partials/topbar")
        @include("admin.layouts.partials/main-nav")

        <div class="page-content pt-2">

            <div class="px-2">

                @include("admin.layouts.partials/page-title")
                @include('admin.layouts.partials.alerts')

                @yield('content')

            </div>

            @include("admin.layouts.partials/footer")

            @yield('modal')
        </div>

    </div>

    @include("admin.layouts.partials/right-sidebar")
    @include('admin.layouts.partials/footer-scripts')

</body>
<script>
    // Set base URL and API routes for JavaScript
    window.appBaseUrl = '{{ url("/") }}';
    window.adminBasePath = 'admin'
    window.apiBaseUrl = '{{ url("/api") }}';
    window.bookingIndexUrl = '{{ route("admin.bookings.index") }}';
</script>
@yield('scripts')

</html>