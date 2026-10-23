@extends('platform.layout')

@section('content')
    @component('platform.urls._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.urls.store') }}">
                {{ csrf_field() }}

                @include('platform.urls._form')

                <div class="row mb-3">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent
@endsection
