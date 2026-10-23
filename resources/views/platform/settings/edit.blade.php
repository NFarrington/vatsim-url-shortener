@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <span class="lead">User Settings</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('platform.settings') }}">
                {{ csrf_field() }}
                {{ method_field('put') }}

                <div class="form-group row">
                    <label for="inputFirstName" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputFirstName" value="{{ $user->getFullName() }}"
                               disabled>
                        <small class="form-text text-muted">
                            To change your name, please contact <a href="https://membership.vatsim.net/">VATSIM
                                Membership</a>.
                        </small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                               id="inputEmail" name="email" value="{{ old('email') ?: $user->getEmail() }}"
                               placeholder="Email">
                        @if ($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @elseif($user->getEmail() && $user->getEmailVerified())
                            <small class="form-text text-success">
                                <i class="material-icons md-18">check</i> Verified
                            </small>
                        @elseif($user->getEmail())
                            <small class="form-text text-danger">
                                <i class="material-icons md-18">close</i> Unverified
                            </small>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label for="staticTwoFactor" class="col-sm-2 col-form-label">Two Factor Authentication</label>
                    <div class="col-sm-10">
                        <p class="form-control-plaintext">
                            @if($user->getTotpSecret())
                                Active &mdash;
                                <a href="#disable-two-factor" data-toggle="collapse" role="button"
                                   aria-expanded="{{ $errors->has('code') ? 'true' : 'false' }}"
                                   aria-controls="disable-two-factor">Disable</a>
                            @elseif($user->getEmail())
                                Disabled &mdash;
                                <a href="{{ route('platform.settings.two-factor') }}">Configure</a>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>

            @if($user->getTotpSecret())
                <form method="POST" action="{{ route('platform.settings.two-factor') }}" id="disable-two-factor"
                      class="collapse{{ $errors->has('code') ? ' show' : '' }}">
                    {{ csrf_field() }}
                    {{ method_field('delete') }}

                    <div class="form-group row">
                        <label for="inputDisableCode" class="col-sm-2 col-form-label">Disable Two Factor</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                                   id="inputDisableCode" name="code" min="0" max="999999" placeholder="123456"
                                   required>
                            @if($errors->has('code'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('code') }}
                                </div>
                            @endif
                            <small class="form-text text-muted">
                                Enter the six-digit code from your authenticator to disable two factor authentication.
                            </small>
                            <button type="submit" class="btn btn-danger mt-2">Disable</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
