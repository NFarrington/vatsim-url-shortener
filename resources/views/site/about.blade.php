@extends('site.layout')

@section('content')
    <h1 class="cover-heading">About VATS.IM</h1>
    <p class="lead">VATS.IM is a URL shortening service for <a href="https://vatsim.net/">VATSIM</a> and its members.
    </p>
    <p>The service has been in use by VATSIM UK since 2014, providing shorter URLs that allow easy and memorable access
        to resources such as documents and charts, and a way of providing pilots with simple links in an Air Traffic
        Controller's ATIS or controller information block.</p>
    <p>Due to the development work required to broaden the service's access, VATS.IM has been restricted to only VATSIM
        UK for the longest time. However, with the appropriate infrastructure now in place, the service is ready for all
        regions, divisions, ARTCCs, vACCs, controllers, and pilots to make use of what it has to offer.</p>
    <p>To get started, please log in below.</p>
    <p>
        <a href="{{ route('platform.login.vatsim-connect') }}" class="btn btn-lg btn-secondary">
            Login via VATSIM
        </a>
    </p>

@endsection
