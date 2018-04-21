@extends('platform.layout')

@section('content')
    @component('platform.organizations._card')
        @include('platform.organizations._table')
    @endcomponent
@endsection
