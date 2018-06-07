@extends('platform.layout')

@section('content')
    @component('platform.organizations.prefix._card')
        <div class="card-body">
            <form method="POST" action="{{ route('platform.organizations.prefix.store', $organization) }}">
                {{ csrf_field() }}

                @include('platform.organizations.prefix._form')

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Submit for Approval</button>
                    </div>
                </div>
            </form>
        </div>
    @endcomponent
@endsection
