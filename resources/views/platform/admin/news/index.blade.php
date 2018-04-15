@extends('platform.layout')

@section('content')
    @component('platform.admin.news._card')
        @include('platform.admin.news._table')
    @endcomponent
@endsection
