@extends('platform.layout')

@section('content')
    @component('platform.admin.prefix-applications._card')
        @include('platform.admin.prefix-applications._table')
    @endcomponent
@endsection
