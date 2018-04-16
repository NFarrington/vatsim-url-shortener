@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header"><span class="lead">Login</span></div>
        <div class="card-body text-center">
            <p class="card-text">
                To continue, please log in below, or <a href="{{ route('site.home') }}">return to the main site</a>.
            </p>
            <a href="{{ route('platform.login.vatsim') }}" class="btn btn-lg btn-primary"
               onclick="event.preventDefault(); document.getElementById('vatsim-login-form').submit();">
                Login via VATSIM
            </a>
            <form id="vatsim-login-form" action="{{ route('platform.login.vatsim') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </div>
    </div>
@endsection
