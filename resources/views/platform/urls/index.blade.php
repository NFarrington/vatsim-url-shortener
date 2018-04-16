@extends('platform.layout')

@section('content')
    @component('platform.urls._card')
        @include('platform.urls._table')
    @endcomponent

    @if($publicUrls->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <span class="lead">Public URLs</span>
                <small class="text-muted">
                    A collection of URLs which may be of use to all users.
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>URL</th>
                        <th>Redirect</th>
                    </tr>
                    @foreach($publicUrls as $url)
                        <tr>
                            <td class="break-all"><a href="{{ url($url->full_url) }}">{{ $url->full_url }}</a></td>
                            <td class="break-all"><a href="{{ $url->redirect_url }}">{{ $url->redirect_url }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif
@endsection
