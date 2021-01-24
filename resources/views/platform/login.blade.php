@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header"><span class="lead">Login</span></div>
        <div class="card-body text-center">
            <p class="card-text">
                To continue, please log in below, or <a href="{{ route('site.home') }}">return to the main site</a>.
            </p>
            <p>
                <a href="{{ route('platform.login.vatsim-connect') }}" class="btn btn-lg btn-primary">
                    Login via VATSIM Connect
                </a>
            </p>
        </div>
    </div>
@endsection
