@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <span class="lead">Two Factor Authentication</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('login.two-factor') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="code" class="control-label">Authentication Code</label>

                    <input id="code" type="number" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                           name="code" min="0" max="999999" required autofocus>
                    @if($errors->has('code'))
                        <div class="invalid-feedback">
                            {{ $errors->first('code') }}
                        </div>
                    @endif
                    <small class="form-text text-muted">
                        Open your two factor authentication app to view your code.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">Verify</button>
            </form>
        </div>
    </div>
@endsection
