@extends('site.layout')

@section('content')
    <h1 class="cover-heading">A URL Shortener for VATSIM</h1>
    <p class="lead">VATS.IM is a URL shortening service for <a href="https://vatsim.net/">VATSIM</a> and its members.</p>
    <p>To continue, please log in with your VATSIM account.</p>
    <p>
        <a href="{{ route('platform.login.vatsim-connect') }}" class="btn btn-lg btn-secondary">
            Login via VATSIM
        </a>
    </p>
@endsection
