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
            <h3 class="masthead-brand">{{ config('app.name') }}</h3>
            <nav class="nav nav-masthead justify-content-center">
                <a class="nav-link{{ Request::routeIs('home') ? ' active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="nav-link{{ Request::routeIs('about') ? ' active' : '' }}" href="{{ route('about') }}">About</a>
                <a class="nav-link{{ Request::routeIs('contact') ? ' active' : '' }}" href="{{ route('contact') }}">Contact</a>
                <a class="nav-link" href="#">Login</a>
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
