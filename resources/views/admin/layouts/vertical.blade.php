<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.layouts.partials/title-meta', ['title' => $title])
    @include('admin.layouts.partials.head-cdn-link')
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
    window.adminBasePath = 'ppadmlog'
    window.apiBaseUrl = '{{ url("/api") }}';
    window.bookingIndexUrl = '{{ route("admin.bookings.index") }}';
</script>

<script>
    // Global: double-click to copy any element with .dblclick-copy
    (function () {
        async function copyTextToClipboard(text) {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                    return true;
                }
            } catch (e) {
                // ignore and fallback
            }

            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(textarea);
                return ok;
            } catch (e) {
                return false;
            }
        }

        document.addEventListener('dblclick', async function (e) {
            const el = e.target.closest('.dblclick-copy');
            if (!el) return;

            const text = el.getAttribute('data-copy-text') || (el.textContent || '').trim();
            if (!text) return;

            const ok = await copyTextToClipboard(text);

            // If SweetAlert2 is available on the page, show a toast. Otherwise keep silent.
            if (ok && window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copied',
                    showConfirmButton: false,
                    timer: 1200
                });
            }
        });
    })();
</script>
@yield('scripts')

</html>