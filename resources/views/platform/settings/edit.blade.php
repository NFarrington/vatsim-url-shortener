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
                        <input type="text" class="form-control" id="inputFirstName" value="{{ $user->full_name }}"
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
                               id="inputEmail" name="email" value="{{ old('email') ?: $user->email }}"
                               placeholder="Email">
                        @if ($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @elseif($user->email && $user->email_verified)
                            <small class="form-text text-success">
                                <i class="material-icons md-18">check</i> Verified
                            </small>
                        @elseif($user->email)
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
                            @if($user->totp_secret)
                                Active &mdash;
                                <delete-resource link-only route="{{ route('platform.settings.two-factor') }}"
                                                 message="Are you sure you want to disable two factor authentication?">
                                    Disable
                                </delete-resource>
                            @elseif($user->email)
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
        </div>
    </div>
@endsection
