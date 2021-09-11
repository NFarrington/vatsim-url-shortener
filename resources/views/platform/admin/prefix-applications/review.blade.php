@extends('platform.layout')

@section('content')
@component('platform.admin.prefix-applications._card')
<div class="card-body">
    @include('platform.admin.prefix-applications._info')
</div>
@endcomponent

<div class="card mt-4">
    <div class="card-header">
        <span class="lead">Approve Application</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('platform.admin.prefix-applications.approve', $prefixApplication) }}">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="inputPrefix" class="col-sm-2 col-form-label">Prefix</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control{{ $errors->has('prefix') ? ' is-invalid' : '' }}"
                           id="inputPrefix" name="prefix" value="{{ old('prefix') ?: $prefixApplication->getPrefix() }}"
                           required maxlength="250" autofocus>
                    @if ($errors->has('prefix'))
                    <div class="invalid-feedback">
                        {{ $errors->first('prefix') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="card mt-4">
    <div class="card-header">
        <span class="lead">Reject Application</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('platform.admin.prefix-applications.reject', $prefixApplication) }}">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="inputReason" class="col-sm-2 col-form-label">Reason</label>
                <div class="col-sm-10">
                    <textarea type="text" class="form-control{{ $errors->has('reason') ? ' is-invalid' : '' }}"
                              id="inputReason" name="reason" required
                              maxlength="100" rows="3">{{ old('reason') }}</textarea>
                    @if ($errors->has('reason'))
                    <div class="invalid-feedback">
                        {{ $errors->first('reason') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
