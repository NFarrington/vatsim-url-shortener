@extends('platform.layout')

@section('content')
    @component('platform.urls._card')
        @include('platform.urls._table')
    @endcomponent
@endsection
