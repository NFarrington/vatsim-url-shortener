@extends('errors::layout')

@section('title', 'Service Unavailable')

@section('message')
    @if(!empty($exception->getMessage()))
        {{ $exception->getMessage() }}<br><br>
    @else
        Down for maintenance. Be right back!<br><br>
    @endif

    @if(!empty($exception->retryAfter))
        Please try again {{ $exception->wentDownAt->addSeconds($exception->retryAfter)->diffForHumans() }}.
    @endif
@endsection
