@extends('platform.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <span class="lead">Two Factor Authentication</span>
        </div>
        <div class="card-body">
            <p>To set up two-factor authentication, scan the code below using your authenticator app.</p>
            <p>If you don't have one, <a href="https://support.google.com/accounts/answer/1066447"
                                         target="_blank">click here</a> to set up Google Authenticator.</p>

            <div>
                <img src="{{ $qrCode }}" alt="QR Code" class="img-fluid mx-auto d-block">
            </div>

            <p>If you are unable to scan the barcode, enter the following code instead:
                <reveal-text hidden="{{ $secret }}"></reveal-text>
            </p>

            <form method="POST" action="{{ route('platform.settings.two-factor') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="code" class="control-label">Enter the six-digit code from your
                        authenticator</label>

                    <input id="code" type="number" class="form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                           name="code" min="0" max="999999" placeholder="123456" required autofocus>
                    @if($errors->has('code'))
                        <div class="invalid-feedback">
                            {{ $errors->first('code') }}
                        </div>
                    @endif
                    <small class="form-text text-muted">
                        After scanning the barcode image, the app will display a six-digit code that you can enter
                        above.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">Enable</button>
            </form>
        </div>
    </div>
@endsection
