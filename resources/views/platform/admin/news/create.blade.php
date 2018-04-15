@extends('platform.layout')

@section('content')
    @component('platform.admin.news._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.admin.news.store') }}">
                {{ csrf_field() }}

                @include('platform.admin.news._form')

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent
@endsection
