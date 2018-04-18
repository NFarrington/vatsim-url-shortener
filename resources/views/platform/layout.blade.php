<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-T62XX6L');</script>
    <!-- End Google Tag Manager -->

    <meta name="author" content="Neil Farrington">
    <meta name="description" content="VATS.IM is a URL shortening service for the VATSIM network.">
    <meta property="og:title" content="VATS.IM URL Shortener">
    <meta property="og:description" content="A URL shortener for VATSIM">
    <meta property="og:image" content="/img/cover.jpg">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbdd9">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#ffffff">

    <title>{{ config('app.name') }} - {{ breadcrumbs() }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @stack('meta')
</head>
<body>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T62XX6L"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="app">
    @include('platform.layout-navbar')

    @include('platform.layout-breadcrumbs')

    <main class="container mb-4" role="main">
        @yield('content')
    </main>

    <footer>
        <div class="text-center text-muted">
            <p>&copy; {{ date('Y') }} Neil Farrington &mdash; Version {{ config('app.version.name') }} (<a href="https://github.com/NFarrington/vatsim-url-shortener/commits/{{ config('app.version.commit') }}">#{{ config('app.version.commit') }}</a>)</p>
            <p><a href="{{ route('platform.terms') }}">Terms of Use</a> &ndash; <a href="{{ route('platform.privacy') }}">Privacy &amp; Cookies</a></p>
        </div>
    </footer>

    <flash message="{{ session('success') }}" level="success"></flash>
    <flash message="{{ session('error') }}" level="danger"></flash>
</div>

<script src="{{ mix('js/app.js') }}"></script>
@stack('scripts')

</body>
</html>
