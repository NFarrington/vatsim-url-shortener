<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbdd9">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#ffffff">

    <title>{{ config('app.name', 'Laravel') }} - {{ breadcrumbs() }}</title>

    <link rel="stylesheet" href="{{ mix('css/cover.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="text-center">

<div id="app" class="cover-container d-flex p-3 mx-auto flex-column">
    <header class="masthead mb-auto">
        <div class="inner">
            <h3 class="masthead-brand"><a href="{{ route('site.home') }}">{{ config('app.name') }}</a></h3>
            <nav class="nav nav-masthead justify-content-center">
                <a class="nav-link{{ Request::routeIs('site.home') ? ' active' : '' }}" href="{{ route('site.home') }}">Home</a>
                <a class="nav-link{{ Request::routeIs('site.about') ? ' active' : '' }}" href="{{ route('site.about') }}">About</a>
                <a class="nav-link{{ Request::routeIs('site.contact') ? ' active' : '' }}" href="{{ route('site.contact') }}">Contact</a>
                <a href="{{ route('platform.login.vatsim') }}" class="nav-link"
                   onclick="event.preventDefault(); document.getElementById('vatsim-login-form').submit();">
                    Login
                </a>
                <form id="vatsim-login-form" action="{{ route('platform.login.vatsim') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </nav>
        </div>
    </header>

    <main role="main" class="inner cover">
        @yield('content')
    </main>

    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p>&copy; {{ date('Y') }} Neil Farrington</p>
        </div>
    </footer>
</div>

<script src="{{ mix('js/app.js') }}"></script>

</body>
</html>
