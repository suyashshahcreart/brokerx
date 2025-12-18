<!DOCTYPE html>
<html lang="en" @yield('html_attribute')>

<head>
    @include('frontend.layouts.partials/title-meta', ['title' => $title])
    @include('frontend.layouts.partials/head-css')

    <!-- New theme (global) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montagu+Slab:opsz,wght@16..144,100..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google reCAPTCHA v3 (from demo theme) -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LcpuycsAAAAAAJFaBunTz63ks3_lubeAQGfrb0z"></script>
    <!-- Google Tag Manager (from demo theme) -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5MKC5T7N');
    </script>

    @yield('css')

    <!-- New theme CSS should load after page/vendor CSS (matches demo order) -->
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/styles.css') }}">
</head>

<body @yield('body_attribute')>

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5MKC5T7N"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

@include('frontend.layouts.partials.header')

@yield('content')

@include('frontend.layouts.partials.footer')

@include('frontend.layouts.partials/footer-scripts')

@yield('scripts')

</body>

</html>
