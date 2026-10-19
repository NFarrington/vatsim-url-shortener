@extends('platform.layout')

@section('content')
    @component('platform.admin.organizations._card')
        @include('platform.admin.organizations._table')
    @endcomponent
@endsection
