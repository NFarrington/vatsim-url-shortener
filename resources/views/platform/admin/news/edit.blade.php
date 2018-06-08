@extends('platform.layout')

@section('content')
    @component('platform.admin.news._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.admin.news.update', $news) }}">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                @include('platform.admin.news._form')

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent

    @include('platform._news-card', ['post' => $news])
@endsection
