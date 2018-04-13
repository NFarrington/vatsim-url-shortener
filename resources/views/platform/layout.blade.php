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

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @stack('meta')
</head>
<body>

<div id="app">
    @include('platform.layout-navbar')

    <main class="container" role="main">
        @include('platform.layout-alerts')

        @yield('content')
    </main>
</div>

<script src="{{ mix('js/app.js') }}"></script>
@stack('scripts')

</body>
</html>
