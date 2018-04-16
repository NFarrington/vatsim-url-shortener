@extends('platform.layout')

@section('content')
    @component('platform.admin.domains._card')
        @include('platform.admin.domains._table')
    @endcomponent
@endsection
