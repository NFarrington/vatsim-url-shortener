@extends('platform.layout')

@section('title', config('app.name') . ' - Error')

@section('no-navigation', true)

@section('content')
<div class="card">
    <div class="card-header"><span class="lead">Server Error</span></div>
    <div class="card-body">
        <p class="card-text">Sorry! An error occurred while loading your request.</p>
        <p class="card-text">If the issue continues, please contact <a href="mailto:support@vats.im">support@vats.im</a>.</p>
        <p class="card-text">Short URLs are served by a separate system to the URL management platform.
            When the management platform is unavailable, requests to short URLs are still served as normal.</p>
    </div>
</div>
@endsection
