@extends('platform.layout')

@section('content')
    @component('platform.urls._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.urls.update', $url) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                @include('platform.urls._form')

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent
@endsection
