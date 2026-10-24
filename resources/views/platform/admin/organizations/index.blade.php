@extends('platform.layout')

@section('content')
    @component('platform.admin.organizations._card')
        <div class="card-body">
            <form method="GET" action="{{ route('platform.admin.organizations.index') }}" role="search">
                @php($sortQuery = array_filter(Arr::only(Request::query(), ['sort', 'direction']), 'is_string'))
                @foreach($sortQuery as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <div class="input-group">
                    <input type="search" class="form-control" name="q" value="{{ $search }}"
                           placeholder="Search by name, prefix, ID, or member name or CID" aria-label="Search organizations">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if($search !== null && $search !== '')
                        <a class="btn btn-outline-secondary"
                           href="{{ route('platform.admin.organizations.index', $sortQuery) }}">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @include('platform.admin.organizations._table')
    @endcomponent
@endsection
