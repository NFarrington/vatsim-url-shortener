@extends('platform.layout')

@section('content')
    @component('platform.organizations._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.organizations.update', $organization) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                @include('platform.organizations._form-edit')
            </form>
        </div>
    @endcomponent

    @include('platform.organizations._add-user', ['action' => route('platform.organizations.users.store', $organization)])
@endsection
