@extends('platform.layout')

@section('content')
    @component('platform.organizations._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.organizations.store') }}">
                {{ csrf_field() }}

                @include('platform.organizations._form-create')

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent
@endsection
