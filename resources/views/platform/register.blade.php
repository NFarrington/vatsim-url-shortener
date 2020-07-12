@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header"><span class="lead">Email Address</span></div>
        <div class="card-body col-sm-8 offset-sm-2">
            <form method="POST" action="{{ route('platform.register') }}">
                {{ csrf_field() }}

                @if(!$user->getEmail())
                    <p>To continue, please enter your email address.</p>
                @endif

                <div class="form-group">
                    <label for="inputEmail">Email</label>
                    <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           id="inputEmail" name="email" value="{{ old('email') ?: $user->getEmail() }}" placeholder="Email"
                           required autofocus>
                    @if($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    @if($user->getEmail())
                        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                    @else
                        <button type="submit" class="btn btn-primary">Submit</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
