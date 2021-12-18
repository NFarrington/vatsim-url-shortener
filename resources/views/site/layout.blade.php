<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <meta property="og:image" content="/assets/img/cover.jpg">

    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <link rel="mask-icon" href="/assets/safari-pinned-tab.svg" color="#5bbdd9">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#ffffff">

    <title>{{ config('app.name') }} - {{ breadcrumbs() }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="/assets/{{ config('app.version.name') }}/css/cover.css">

    @stack('meta')
</head>
<body class="text-center">

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T62XX6L"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="app" class="cover-container d-flex p-3 mx-auto flex-column">
    <header class="masthead mb-auto">
        <div class="inner">
            <h3 class="masthead-brand"><a href="{{ route('site.home') }}">{{ config('app.name') }}</a></h3>
            <nav class="nav nav-masthead justify-content-center">
                <a class="nav-link{{ Request::routeIs('site.home') ? ' active' : '' }}" href="{{ route('site.home') }}">Home</a>
                <a class="nav-link{{ Request::routeIs('site.about') ? ' active' : '' }}" href="{{ route('site.about') }}">About</a>
                <a class="nav-link{{ Request::routeIs('site.contact') ? ' active' : '' }}" href="{{ route('site.contact') }}">Contact</a>
                <a href="{{ route('platform.login.vatsim-connect') }}" class="nav-link">
                    Login
                </a>
            </nav>
        </div>
    </header>

    <main role="main" class="inner cover">
        @yield('content')
    </main>

    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p>&copy; {{ date('Y') }} Neil Farrington</p>
            <p><a href="{{ route('platform.terms') }}">Terms of Use</a> &ndash; <a href="{{ route('platform.privacy') }}">Privacy &amp; Cookies</a></p>
        </div>
    </footer>
</div>

@include('common.environment-js')
<script src="/assets/{{ config('app.version.name') }}/js/app.js"></script>

</body>
</html>
