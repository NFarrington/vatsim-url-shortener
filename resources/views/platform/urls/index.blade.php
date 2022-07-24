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
                    A collection of URLs which may be of use to all users. Some URLs may be controlled by VATSIM or other third parties.
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <tr>
                        <th>URL</th>
                        <th>Redirect URL</th>
                    </tr>
                    @foreach($publicUrls as $url)
                        <tr>
                            <td class="break-all"><a href="{{ url($url->getFullUrl()) }}">{{ preg_replace('#^https?://#', '', $url->getFullUrl()) }}</a></td>
                            <td class="break-all"><a href="{{ $url->getRedirectUrl() }}">{{ preg_replace('#^https?://#', '', $url->getRedirectUrl()) }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif
@endsection
